<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('acta_producto') || ! Schema::hasTable('acta_firmas')) {
            throw new RuntimeException('The legacy acta product and signature tables must exist before migrating actas.');
        }

        DB::table('actas')->orderBy('id')->chunkById(100, function ($actas): void {
            foreach ($actas as $acta) {
                $this->tipoActa($acta->tipo);
                $this->firmantes($acta);
            }
        });

        Schema::table('actas', function (Blueprint $table): void {
            $table->string('tipo_acta')->nullable();
            $table->json('productos')->nullable();
            $table->unsignedBigInteger('firmante_1_usuario_id')->nullable();
            $table->unsignedBigInteger('firmante_1_rol_id')->nullable();
            $table->string('firmante_1_rol', 100)->nullable();
            $table->unsignedBigInteger('firmante_2_usuario_id')->nullable();
            $table->unsignedBigInteger('firmante_2_rol_id')->nullable();
            $table->string('firmante_2_rol', 100)->nullable();

            $table->foreign('firmante_1_usuario_id', 'actas_firmante_1_usuario_foreign')
                ->references('id')->on('usuarios')->nullOnDelete();
            $table->foreign('firmante_1_rol_id', 'actas_firmante_1_rol_foreign')
                ->references('id_rol')->on('roles')->nullOnDelete();
            $table->foreign('firmante_2_usuario_id', 'actas_firmante_2_usuario_foreign')
                ->references('id')->on('usuarios')->nullOnDelete();
            $table->foreign('firmante_2_rol_id', 'actas_firmante_2_rol_foreign')
                ->references('id_rol')->on('roles')->nullOnDelete();
        });

        DB::table('actas')->orderBy('id')->chunkById(100, function ($actas): void {
            foreach ($actas as $acta) {
                $productos = DB::table('acta_producto as acta_productos')
                    ->join('productos', 'productos.id', '=', 'acta_productos.producto_id')
                    ->where('acta_productos.acta_id', $acta->id)
                    ->orderBy('acta_productos.id')
                    ->get([
                        'acta_productos.producto_id',
                        'productos.nombre',
                        'acta_productos.cantidad',
                        'acta_productos.observaciones',
                    ])
                    ->map(fn (object $producto): array => [
                        'producto_id' => (int) $producto->producto_id,
                        'nombre' => $producto->nombre,
                        'cantidad' => (float) $producto->cantidad,
                        'observaciones' => $producto->observaciones,
                    ])
                    ->all();

                $firmantes = $this->firmantes($acta);
                DB::table('actas')->where('id', $acta->id)->update([
                    'tipo_acta' => $this->tipoActa($acta->tipo),
                    'productos' => json_encode($productos, JSON_THROW_ON_ERROR),
                    'firmante_1_usuario_id' => $firmantes[0]['usuario_id'],
                    'firmante_1_rol_id' => $firmantes[0]['rol_id'],
                    'firmante_1_rol' => $firmantes[0]['rol_nombre'],
                    'firmante_1_nombre' => $firmantes[0]['nombre'],
                    'firmante_1_cargo' => $firmantes[0]['cargo'],
                    'firmante_2_usuario_id' => $firmantes[1]['usuario_id'],
                    'firmante_2_rol_id' => $firmantes[1]['rol_id'],
                    'firmante_2_rol' => $firmantes[1]['rol_nombre'],
                    'firmante_2_nombre' => $firmantes[1]['nombre'],
                    'firmante_2_cargo' => $firmantes[1]['cargo'],
                ]);
            }
        });

        Schema::table('actas', function (Blueprint $table): void {
            $table->string('tipo_acta')->nullable(false)->change();
            $table->json('productos')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        throw new RuntimeException(
            'This migration is intentionally irreversible because acta data written to JSON must not be discarded. Restore from a verified backup instead.'
        );
    }

    private function tipoActa(string $tipo): string
    {
        return match ($tipo) {
            'entrega_bienes', 'entrega_inmueble' => 'entrega',
            'junta_directiva' => 'junta_directiva',
            'asamblea' => 'asamblea',
            default => throw new RuntimeException("Unsupported legacy acta type: {$tipo}."),
        };
    }

    private function firmantes(object $acta): array
    {
        $firmantes = [
            [
                'usuario_id' => null,
                'rol_id' => null,
                'rol_nombre' => null,
                'nombre' => $acta->firmante_1_nombre,
                'cargo' => $acta->firmante_1_cargo,
            ],
            [
                'usuario_id' => null,
                'rol_id' => null,
                'rol_nombre' => null,
                'nombre' => $acta->firmante_2_nombre,
                'cargo' => $acta->firmante_2_cargo,
            ],
        ];

        $firmas = DB::table('acta_firmas')
            ->where('acta_id', $acta->id)
            ->orderBy('id')
            ->get();

        foreach ($firmas as $firma) {
            $indice = null;
            foreach ([0, 1] as $slot) {
                if (
                    $firmantes[$slot]['nombre'] !== null
                    && $firmantes[$slot]['nombre'] === $firma->nombre
                    && ($firmantes[$slot]['usuario_id'] === null || (int) $firmantes[$slot]['usuario_id'] === (int) $firma->usuario_id)
                ) {
                    $indice = $slot;
                    break;
                }
            }

            if ($indice === null) {
                foreach ([0, 1] as $slot) {
                    if ($firmantes[$slot]['nombre'] === null && $firmantes[$slot]['cargo'] === null) {
                        $indice = $slot;
                        break;
                    }
                }
            }

            if ($indice === null) {
                throw new RuntimeException(
                    "Acta {$acta->id} has more than two distinct legacy signers; resolve the records before migrating."
                );
            }

            $firmantes[$indice] = [
                'usuario_id' => $firma->usuario_id,
                'rol_id' => $firma->rol_id,
                'rol_nombre' => $firma->rol_nombre,
                'nombre' => $firmantes[$indice]['nombre'] ?? $firma->nombre,
                'cargo' => $firmantes[$indice]['cargo'] ?? $firma->cargo,
            ];
        }

        return $firmantes;
    }
};

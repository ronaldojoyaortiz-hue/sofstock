<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('actas', 'firmantes')) {
            Schema::table('actas', function (Blueprint $table): void {
                $table->json('firmantes')->nullable();
            });
        }

        DB::table('actas')->whereNull('firmantes')->orderBy('id')->chunkById(100, function ($actas): void {
            foreach ($actas as $acta) {
                $firmantes = [];
                foreach ([1, 2] as $numero) {
                    $firmante = [
                        'usuario_id' => $acta->{'firmante_'.$numero.'_usuario_id'},
                        'rol_id' => $acta->{'firmante_'.$numero.'_rol_id'},
                        'rol' => $acta->{'firmante_'.$numero.'_rol'},
                        'nombre' => $acta->{'firmante_'.$numero.'_nombre'},
                        'cargo' => $acta->{'firmante_'.$numero.'_cargo'},
                    ];

                    if (collect($firmante)->contains(fn ($valor): bool => $valor !== null && $valor !== '')) {
                        $firmantes[] = $firmante;
                    }
                }

                DB::table('actas')->where('id', $acta->id)->update([
                    'firmantes' => json_encode($firmantes, JSON_THROW_ON_ERROR),
                ]);
            }
        });
    }

    public function down(): void
    {
        throw new RuntimeException(
            'Firmante data is stored in JSON; restore the actas table from a verified backup before removing this column.'
        );
    }
};

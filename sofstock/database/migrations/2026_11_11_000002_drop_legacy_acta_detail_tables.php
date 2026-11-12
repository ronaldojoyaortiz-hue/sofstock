<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('acta_producto') || ! Schema::hasTable('acta_firmas')) {
            throw new RuntimeException('Both legacy acta detail tables must exist before they can be removed.');
        }

        DB::table('actas')->orderBy('id')->chunkById(100, function ($actas): void {
            foreach ($actas as $acta) {
                $productos = json_decode($acta->productos, true, flags: JSON_THROW_ON_ERROR);
                $productosUsados = [];

                foreach (DB::table('acta_producto')->where('acta_id', $acta->id)->get() as $productoLegado) {
                    $coincidencias = [];
                    foreach ($productos as $indice => $producto) {
                        if (
                            (int) ($producto['producto_id'] ?? 0) === (int) $productoLegado->producto_id
                            && number_format((float) ($producto['cantidad'] ?? 0), 2, '.', '') === number_format((float) $productoLegado->cantidad, 2, '.', '')
                            && ($producto['observaciones'] ?? null) === $productoLegado->observaciones
                            && is_string($producto['nombre'] ?? null)
                        ) {
                            $coincidencias[] = $indice;
                        }
                    }

                    if (count($coincidencias) !== 1 || isset($productosUsados[$coincidencias[0]])) {
                        throw new RuntimeException(
                            "Legacy products for acta {$acta->id} are not represented exactly once in actas.productos."
                        );
                    }
                    $productosUsados[$coincidencias[0]] = true;
                }

                foreach (DB::table('acta_firmas')->where('acta_id', $acta->id)->get() as $firmaLegada) {
                    $coincide = false;
                    foreach ([1, 2] as $numero) {
                        if (
                            $acta->{'firmante_'.$numero.'_nombre'} === $firmaLegada->nombre
                            && $acta->{'firmante_'.$numero.'_cargo'} === $firmaLegada->cargo
                            && ($firmaLegada->usuario_id === null || (int) $acta->{'firmante_'.$numero.'_usuario_id'} === (int) $firmaLegada->usuario_id)
                            && ($firmaLegada->rol_id === null || (int) $acta->{'firmante_'.$numero.'_rol_id'} === (int) $firmaLegada->rol_id)
                            && ($firmaLegada->rol_nombre === null || $acta->{'firmante_'.$numero.'_rol'} === $firmaLegada->rol_nombre)
                        ) {
                            $coincide = true;
                            break;
                        }
                    }

                    if (! $coincide) {
                        throw new RuntimeException(
                            "Legacy signatures for acta {$acta->id} are not fully represented in the signer columns of actas."
                        );
                    }
                }
            }
        });

        Schema::dropIfExists('acta_producto');
        Schema::dropIfExists('acta_firmas');
    }

    public function down(): void
    {
        throw new RuntimeException(
            'Legacy acta detail tables were dropped; restore them only from a verified backup.'
        );
    }
};

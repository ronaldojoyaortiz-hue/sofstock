<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('facturas', 'porcentaje_impuesto')) {
            Schema::table('facturas', function (Blueprint $table) {
                $table->dropColumn('porcentaje_impuesto');
            });
        }

        $columnasDetalle = [
            'producto_nombre' => fn(Blueprint $table) => $table->string('producto_nombre', 150)->nullable(),
            'porcentaje_impuesto' => fn(Blueprint $table) => $table->decimal('porcentaje_impuesto', 5, 2)->default(0),
            'descuento' => fn(Blueprint $table) => $table->decimal('descuento', 12, 2)->default(0),
        ];

        foreach ($columnasDetalle as $nombre => $agregarColumna) {
            if (! Schema::hasColumn('detalle_facturas', $nombre)) {
                Schema::table('detalle_facturas', $agregarColumna);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('facturas', 'porcentaje_impuesto')) {
            Schema::table('facturas', function (Blueprint $table) {
                $table->decimal('porcentaje_impuesto', 5, 2)->default(0);
            });
        }

        foreach (['producto_nombre', 'porcentaje_impuesto', 'descuento'] as $columna) {
            if (Schema::hasColumn('detalle_facturas', $columna)) {
                Schema::table('detalle_facturas', function (Blueprint $table) use ($columna) {
                    $table->dropColumn($columna);
                });
            }
        }
    }
};

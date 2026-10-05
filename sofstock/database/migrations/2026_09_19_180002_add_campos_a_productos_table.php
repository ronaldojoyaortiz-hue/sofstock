<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columnas = [
            'nombre' => fn (Blueprint $table) => $table->string('nombre', 150)->nullable(),
            'descripcion' => fn (Blueprint $table) => $table->text('descripcion')->nullable(),
            // La relación se valida en la aplicación para mantener compatibilidad.
            'id_categoria' => fn (Blueprint $table) => $table->unsignedBigInteger('id_categoria')->nullable(),
            'stock_actual' => fn (Blueprint $table) => $table->decimal('stock_actual', 12, 2)->default(0),
            'precio_base' => fn (Blueprint $table) => $table->decimal('precio_base', 12, 2)->default(0),
        ];

        foreach ($columnas as $nombre => $agregarColumna) {
            if (! Schema::hasColumn('productos', $nombre)) {
                Schema::table('productos', $agregarColumna);
            }
        }
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'descripcion', 'id_categoria', 'stock_actual', 'precio_base']);
        });
    }
};

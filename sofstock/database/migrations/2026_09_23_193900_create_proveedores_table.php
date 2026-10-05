<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('proveedores')) {
            Schema::create('proveedores', function (Blueprint $table) {
                $table->id('id_proveedor');
                $table->string('razon_social', 150);
                $table->string('nit', 20)->unique()->nullable();
                $table->string('contacto_nombre', 100)->nullable();
                $table->string('correo', 150)->nullable();
                $table->string('telefono', 20)->nullable();
                $table->text('direccion')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamp('fecha_registro')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};

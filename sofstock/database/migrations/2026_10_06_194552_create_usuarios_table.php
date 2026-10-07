<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('correo', 150)->unique();
            $table->string('contrasena');
            $table->unsignedBigInteger('id_rol');
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamp('fecha_actualizacion')->nullable();

            $table->foreign('id_rol')
                ->references('id_rol')
                ->on('roles')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};

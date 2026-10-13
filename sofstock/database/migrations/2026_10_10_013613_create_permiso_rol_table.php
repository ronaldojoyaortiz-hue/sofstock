<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permiso_rol', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_permiso');

            $table->foreign('id_rol')
                ->references('id_rol')
                ->on('roles')
                ->cascadeOnDelete();

            $table->foreign('id_permiso')
                ->references('id_permiso')
                ->on('permisos')
                ->cascadeOnDelete();

            $table->unique(['id_rol', 'id_permiso']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permiso_rol');
    }
};

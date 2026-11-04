<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acta_firmas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_id')->constrained('actas')->cascadeOnDelete();
            $table->foreignId('rol_id')->nullable()->constrained('roles', 'id_rol')->nullOnDelete();
            $table->string('rol_nombre', 100);
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('nombre', 201);
            $table->string('cargo', 100);
            $table->timestamps();

            $table->unique(['acta_id', 'usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acta_firmas');
    }
};

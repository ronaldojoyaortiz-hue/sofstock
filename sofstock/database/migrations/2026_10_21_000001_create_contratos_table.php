<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id('id_contrato');
            $table->foreignId('id_inmueble')
                ->constrained('inmuebles', 'id_inmueble')
                ->cascadeOnDelete();
            $table->string('numero_contrato', 50)->unique();
            $table->string('tipo_contrato', 50);
            $table->string('contraparte', 150);
            $table->string('documento_contraparte', 50)->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->decimal('valor', 12, 2)->nullable();
            $table->string('estado', 30)->default('Vigente');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};

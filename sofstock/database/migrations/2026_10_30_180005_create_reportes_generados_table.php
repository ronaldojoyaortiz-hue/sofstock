<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_generados', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_reporte', 30);
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->json('datos');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id')->on('usuarios')->nullOnDelete();
            $table->index(['tipo_reporte', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_generados');
    }
};

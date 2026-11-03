<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presupuestos_mensuales', function (Blueprint $table) {
            $table->id();
            $table->date('mes')->unique();
            $table->decimal('presupuesto_inicial', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('egresos_mensuales', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('tipo', 30);
            $table->string('descripcion', 180);
            $table->decimal('monto', 14, 2);
            $table->timestamps();

            $table->index(['fecha', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('egresos_mensuales');
        Schema::dropIfExists('presupuestos_mensuales');
    }
};

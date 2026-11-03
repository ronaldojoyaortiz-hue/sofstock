<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_presupuestos', function (Blueprint $table) {
            $table->id();
            $table->date('mes');
            $table->decimal('presupuesto_anterior', 14, 2)->nullable();
            $table->decimal('presupuesto_nuevo', 14, 2);
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id')->on('usuarios')->nullOnDelete();
            $table->index(['mes', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_presupuestos');
    }
};

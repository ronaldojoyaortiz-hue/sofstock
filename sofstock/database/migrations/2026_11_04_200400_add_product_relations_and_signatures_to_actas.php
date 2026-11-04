<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actas', function (Blueprint $table) {
            $table->string('firmante_1_nombre', 150)->nullable();
            $table->string('firmante_1_cargo', 150)->nullable();
            $table->string('firmante_2_nombre', 150)->nullable();
            $table->string('firmante_2_cargo', 150)->nullable();
        });

        Schema::create('acta_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_id')->constrained('actas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $table->decimal('cantidad', 12, 2);
            $table->string('observaciones', 500)->nullable();
            $table->timestamps();
            $table->unique(['acta_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acta_producto');

        Schema::table('actas', function (Blueprint $table) {
            $table->dropColumn([
                'firmante_1_nombre',
                'firmante_1_cargo',
                'firmante_2_nombre',
                'firmante_2_cargo',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mermas_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_producto')->constrained('productos', 'id')->restrictOnDelete();
            $table->date('fecha');
            $table->decimal('cantidad', 12, 2);
            $table->decimal('valor_perdida', 14, 2);
            $table->string('motivo', 180);
            $table->timestamps();

            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mermas_productos');
    }
};

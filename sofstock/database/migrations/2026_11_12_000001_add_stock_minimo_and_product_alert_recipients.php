<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('productos', 'stock_minimo')) {
            Schema::table('productos', function (Blueprint $table): void {
                $table->decimal('stock_minimo', 12, 2)->default(0);
            });
        }

        if (! Schema::hasTable('producto_alerta_stock_usuario')) {
            Schema::create('producto_alerta_stock_usuario', function (Blueprint $table): void {
                $table->foreignId('id_producto')->constrained('productos')->cascadeOnDelete();
                $table->foreignId('id_usuario')->constrained('usuarios')->cascadeOnDelete();
                $table->timestamps();

                $table->primary(['id_producto', 'id_usuario']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_alerta_stock_usuario');

        if (Schema::hasColumn('productos', 'stock_minimo')) {
            Schema::table('productos', function (Blueprint $table): void {
                $table->dropColumn('stock_minimo');
            });
        }
    }
};

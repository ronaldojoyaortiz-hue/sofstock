<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('detalle_facturas')) {
            Schema::create('detalle_facturas', function (Blueprint $table) {
                $table->id('id_detalle_factura');
                $table->foreignId('id_factura')->constrained('facturas', 'id_factura')->cascadeOnDelete();
                $table->foreignId('id_producto')->constrained('productos', 'id');
                $table->string('producto_nombre', 150)->nullable();
                $table->decimal('cantidad', 12, 2);
                $table->decimal('precio_unitario', 12, 2);
                $table->decimal('porcentaje_impuesto', 5, 2)->default(0);
                $table->decimal('descuento', 12, 2)->default(0);
                $table->decimal('subtotal', 12, 2);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_facturas');
    }
};

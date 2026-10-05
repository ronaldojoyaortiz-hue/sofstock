<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('facturas')) {
            Schema::create('facturas', function (Blueprint $table) {
                $table->id('id_factura');
                $table->string('numero', 30)->unique();
                $table->string('cliente_nombre', 150);
                $table->string('cliente_documento', 30)->nullable();
                $table->date('fecha');
                $table->decimal('descuento', 12, 2)->default(0);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('impuesto', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);
                $table->enum('estado', ['borrador', 'emitida', 'pagada', 'anulada'])->default('borrador');
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};

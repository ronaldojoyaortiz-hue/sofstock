<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_emisor_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('id_producto')->nullable()->constrained('productos', 'id')->nullOnDelete();
            $table->string('titulo', 150);
            $table->text('mensaje');
            $table->string('tipo', 30)->default('manual');
            $table->dateTime('fecha_evento')->nullable();
            $table->json('datos')->nullable();
            $table->timestamps();

            $table->index('tipo');
        });

        Schema::create('notificacion_usuario', function (Blueprint $table) {
            $table->foreignId('id_notificacion')->constrained('notificaciones')->cascadeOnDelete();
            $table->foreignId('id_usuario')->constrained('usuarios')->cascadeOnDelete();
            $table->dateTime('leida_at')->nullable();
            $table->timestamps();

            $table->primary(['id_notificacion', 'id_usuario']);
            $table->index(['id_usuario', 'leida_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacion_usuario');
        Schema::dropIfExists('notificaciones');
    }
};

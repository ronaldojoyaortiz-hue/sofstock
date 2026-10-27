<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->bigIncrements('id_auditoria');

            $table->unsignedBigInteger('id_usuario')->nullable();

            $table->string('accion', 50);
            $table->string('modulo', 100);
            $table->string('descripcion', 500);

            $table->string('tabla_afectada', 100)->nullable();
            $table->string('id_registro_afectado', 100)->nullable();

            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();

            $table->string('motivo', 500)->nullable();
            $table->string('direccion_ip', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['id_usuario', 'created_at']);
            $table->index(['modulo', 'created_at']);
            $table->index('accion');

            $table->foreign('id_usuario')
                ->references('id')
                ->on('usuarios')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};

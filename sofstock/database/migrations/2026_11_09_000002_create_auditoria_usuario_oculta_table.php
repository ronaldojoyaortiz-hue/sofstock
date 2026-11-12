<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_usuario_oculta', function (Blueprint $table): void {
            $table->unsignedBigInteger('id_auditoria');
            $table->unsignedBigInteger('id_usuario');
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['id_auditoria', 'id_usuario']);
            $table->index(['id_usuario', 'created_at']);
            $table->foreign('id_auditoria')
                ->references('id_auditoria')
                ->on('auditorias')
                ->cascadeOnDelete();
            $table->foreign('id_usuario')
                ->references('id')
                ->on('usuarios')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_usuario_oculta');
    }
};

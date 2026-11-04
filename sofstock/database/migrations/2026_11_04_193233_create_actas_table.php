
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actas', function (Blueprint $table) {
            $table->id();

            $table->string('codigo')->unique();

            $table->enum('tipo', [
                'entrega_bienes',
                'junta_directiva',
                'asamblea',
                'entrega_inmueble'
            ]);

            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->string('lugar')->nullable();

            $table->string('titulo');
            $table->text('objetivo')->nullable();
            $table->longText('desarrollo')->nullable();
            $table->longText('conclusiones')->nullable();

            $table->enum('estado', [
                'borrador',
                'pendiente_aprobacion',
                'aprobada',
                'firmada',
                'anulada'
            ])->default('borrador');

            $table->foreignId('usuario_id')
                ->constrained('usuarios')
                ->restrictOnDelete();

            $table->string('usuario_nombre');

            $table->unsignedBigInteger('rol_id')->nullable();
            $table->string('rol_nombre')->nullable();

            $table->foreign('rol_id')
                ->references('id_rol')
                ->on('roles')
                ->nullOnDelete();

            $table->unsignedBigInteger('inmueble_id')->nullable();

            $table->foreign('inmueble_id')
                ->references('id_inmueble')
                ->on('inmuebles')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actas');
    }
};

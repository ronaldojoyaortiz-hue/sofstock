
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inmuebles', function (Blueprint $table) {
            $table->id('id_inmueble');

            $table->string('codigo', 30)->unique();
            $table->string('tipo_inmueble', 50);
            $table->text('descripcion')->nullable();

            $table->string('direccion', 255);
            $table->string('ciudad', 100);
            $table->decimal('area_m2', 10, 2)->nullable();

            $table->string('estado', 30)->default('Disponible');

            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inmuebles');
    }
};

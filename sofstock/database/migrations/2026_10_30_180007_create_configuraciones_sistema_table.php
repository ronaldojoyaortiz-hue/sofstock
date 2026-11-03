<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones_sistema', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('nombre_empresa', 120)->default('SofStock');
            $table->string('moneda', 3)->default('COP');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones_sistema');
    }
};

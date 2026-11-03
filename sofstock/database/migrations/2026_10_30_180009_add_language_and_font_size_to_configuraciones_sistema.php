<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuraciones_sistema', function (Blueprint $table) {
            $table->string('idioma', 5)->default('es');
            $table->unsignedTinyInteger('tamano_letra')->default(16);
        });
    }

    public function down(): void
    {
        Schema::table('configuraciones_sistema', function (Blueprint $table) {
            $table->dropColumn(['idioma', 'tamano_letra']);
        });
    }
};

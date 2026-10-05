<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->string('razon_social', 150)->change();
            $table->string('correo', 150)->nullable()->change();
            $table->string('telefono', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->string('razon_social', 20)->change();
            $table->string('correo', 30)->nullable()->change();
            $table->string('telefono', 10)->nullable()->change();
        });
    }
};
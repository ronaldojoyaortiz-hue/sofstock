<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            if (!Schema::hasColumn('proveedores', 'contacto_nombre')) {
                $table->string('contacto_nombre', 100)->nullable()->after('nit');
            }
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropColumn('contacto_nombre');
        });
    }
};

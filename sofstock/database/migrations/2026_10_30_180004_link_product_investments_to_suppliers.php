<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('egresos_mensuales', function (Blueprint $table) {
            if (! Schema::hasColumn('egresos_mensuales', 'id_producto')) {
                $table->foreignId('id_producto')->nullable()->constrained('productos', 'id')->nullOnDelete();
            }
            if (! Schema::hasColumn('egresos_mensuales', 'id_proveedor')) {
                $table->foreignId('id_proveedor')->nullable()->constrained('proveedores', 'id_proveedor')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('egresos_mensuales', function (Blueprint $table) {
            if (Schema::hasColumn('egresos_mensuales', 'id_producto')) {
                $table->dropConstrainedForeignId('id_producto');
            }
            if (Schema::hasColumn('egresos_mensuales', 'id_proveedor')) {
                $table->dropConstrainedForeignId('id_proveedor');
            }
        });
    }
};

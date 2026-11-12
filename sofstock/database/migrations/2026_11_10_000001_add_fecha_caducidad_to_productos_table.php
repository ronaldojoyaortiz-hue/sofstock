<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('productos', 'fecha_caducidad')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->date('fecha_caducidad')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('productos', 'fecha_caducidad')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->dropColumn('fecha_caducidad');
            });
        }
    }
};

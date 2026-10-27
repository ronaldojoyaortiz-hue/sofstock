<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('contratos', 'archivos')) {
            Schema::table('contratos', function (Blueprint $table) {
                $table->longText('archivos')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('contratos', 'archivos')) {
            Schema::table('contratos', function (Blueprint $table) {
                $table->dropColumn('archivos');
            });
        }
    }
};

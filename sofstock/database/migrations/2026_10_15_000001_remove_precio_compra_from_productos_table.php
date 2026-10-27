<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('productos', 'precio_compra')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->dropColumn('precio_compra');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('productos', 'precio_compra')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->decimal('precio_compra', 12, 2)->default(0);
            });
        }
    }
};

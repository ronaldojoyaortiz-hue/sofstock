<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('_usuario') && DB::table('_usuario')->exists()) {
            throw new RuntimeException(
                'No se eliminó la tabla _usuario porque contiene registros.'
            );
        }

        Schema::dropIfExists('_usuario');
    }

    public function down(): void
    {
        if (Schema::hasTable('_usuario')) {
            return;
        }

        Schema::create('_usuario', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });
    }
};

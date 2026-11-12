<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actas', function (Blueprint $table): void {
            $table->dropForeign(['usuario_id']);
            $table->unsignedBigInteger('usuario_id')->nullable()->change();
            $table->foreign('usuario_id')
                ->references('id')
                ->on('usuarios')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('actas')->whereNull('usuario_id')->exists()) {
            throw new \RuntimeException(
                'Cannot restore the required acta owner while deleted users have preserved actas.'
            );
        }

        Schema::table('actas', function (Blueprint $table): void {
            $table->dropForeign(['usuario_id']);
            $table->unsignedBigInteger('usuario_id')->nullable(false)->change();
            $table->foreign('usuario_id')
                ->references('id')
                ->on('usuarios')
                ->restrictOnDelete();
        });
    }
};

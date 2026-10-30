<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException('This migration requires MySQL to normalize relationship columns.');
        }

        if (Schema::hasColumn('productos', 'id_proveedor') && Schema::hasColumn('proveedores', 'id_proveedor')) {
            $this->normalizeAndAddForeignKey(
                'proveedores',
                'id_proveedor',
                'productos_id_proveedor_foreign'
            );
        }

        if (Schema::hasColumn('productos', 'id_categoria') && Schema::hasColumn('categorias', 'id_categoria')) {
            $this->normalizeAndAddForeignKey(
                'categorias',
                'id_categoria',
                'productos_id_categoria_foreign'
            );
        }
    }

    private function normalizeAndAddForeignKey(string $parentTable, string $column, string $foreignKey): void
    {
        if ($this->hasForeignKey('productos', $foreignKey)) {
            Schema::table('productos', function (Blueprint $table) use ($foreignKey) {
                $table->dropForeign($foreignKey);
            });
        }

        // The parent migrations define these IDs as unsigned BIGINTs. Normalize both
        // columns because existing databases may have been created with older INTs.
        DB::statement("ALTER TABLE `{$parentTable}` MODIFY `{$column}` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
        DB::statement("ALTER TABLE `productos` MODIFY `{$column}` BIGINT UNSIGNED NULL");

        Schema::table('productos', function (Blueprint $table) use ($parentTable, $column, $foreignKey) {
            $table->foreign($column, $foreignKey)
                ->references($column)
                ->on($parentTable)
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        foreach (['productos_id_categoria_foreign', 'productos_id_proveedor_foreign'] as $foreignKey) {
            if ($this->hasForeignKey('productos', $foreignKey)) {
                Schema::table('productos', function (Blueprint $table) use ($foreignKey) {
                    $table->dropForeign($foreignKey);
                });
            }
        }
    }

    private function hasForeignKey(string $table, string $foreignKey): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::connection()->getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $foreignKey)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
};

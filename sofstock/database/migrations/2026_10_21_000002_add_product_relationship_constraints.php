<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('productos', 'id_proveedor') && Schema::hasColumn('proveedores', 'id_proveedor')) {
            $tipoIdProveedor = Schema::getColumnType('proveedores', 'id_proveedor');

            Schema::table('productos', function (Blueprint $table) use ($tipoIdProveedor) {
                if ($tipoIdProveedor === 'integer') {
                    $table->unsignedInteger('id_proveedor')->nullable()->change();
                } elseif ($tipoIdProveedor === 'bigint') {
                    $table->unsignedBigInteger('id_proveedor')->nullable()->change();
                }
            });
        }

        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'id_proveedor') && $this->hasForeignKey('productos', 'productos_id_proveedor_foreign')) {
                $table->dropForeign('productos_id_proveedor_foreign');
            }
        });

        if (Schema::hasColumn('productos', 'id_proveedor') && ! $this->hasForeignKey('productos', 'productos_id_proveedor_foreign')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->foreign('id_proveedor', 'productos_id_proveedor_foreign')
                    ->references('id_proveedor')
                    ->on('proveedores')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasColumn('productos', 'id_categoria') && ! $this->hasForeignKey('productos', 'productos_id_categoria_foreign')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->foreign('id_categoria', 'productos_id_categoria_foreign')
                    ->references('id_categoria')
                    ->on('categorias')
                    ->nullOnDelete();
            });
        }
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

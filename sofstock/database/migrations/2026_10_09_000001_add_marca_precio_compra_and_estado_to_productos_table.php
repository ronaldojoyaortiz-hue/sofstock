<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'marca' => fn (Blueprint $table) => $table->string('marca', 100)->nullable(),
            'precio_compra' => fn (Blueprint $table) => $table->decimal('precio_compra', 12, 2)->default(0),
            'activo' => fn (Blueprint $table) => $table->boolean('activo')->default(true),
        ];

        foreach ($columns as $name => $addColumn) {
            if (! Schema::hasColumn('productos', $name)) {
                Schema::table('productos', $addColumn);
            }
        }
    }

    public function down(): void
    {
        $columns = ['marca', 'precio_compra'];
        $existingColumns = array_filter($columns, fn (string $column) => Schema::hasColumn('productos', $column));

        if ($existingColumns !== []) {
            Schema::table('productos', function (Blueprint $table) use ($existingColumns) {
                $table->dropColumn($existingColumns);
            });
        }
    }
};

<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StockMinimoMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('productos', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('usuarios', function (Blueprint $table): void {
            $table->id();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('producto_alerta_stock_usuario');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('usuarios');

        parent::tearDown();
    }

    public function test_migration_adds_stock_minimo_and_alert_recipient_table(): void
    {
        $migration = require database_path(
            'migrations/2026_11_12_000001_add_stock_minimo_and_product_alert_recipients.php'
        );
        $migration->up();

        $this->assertTrue(Schema::hasColumn('productos', 'stock_minimo'));
        $this->assertTrue(Schema::hasTable('producto_alerta_stock_usuario'));
    }
}

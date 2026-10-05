<?php

namespace Tests\Unit;

use App\Models\Facturas;
use App\Models\Producto;
use App\Repositories\FacturaRepository;
use App\Services\FacturaService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Mockery;
use Tests\TestCase;

class FacturaServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('productos')) {
            Schema::create('productos', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->text('descripcion')->nullable();
                $table->unsignedBigInteger('id_categoria')->nullable();
                $table->unsignedBigInteger('id_proveedor')->nullable();
                $table->decimal('stock_actual', 12, 2)->default(0);
                $table->decimal('stock_minimo', 12, 2)->default(0);
                $table->string('unidad_base')->nullable();
                $table->decimal('precio_base', 12, 2)->default(0);
                $table->boolean('activo')->default(true);
                $table->timestamp('fecha_registro')->nullable();
                $table->timestamp('fecha_actualizacion')->nullable();
            });
        }

        if (! Schema::hasTable('facturas')) {
            Schema::create('facturas', function (Blueprint $table) {
                $table->id('id_factura');
                $table->string('numero', 30)->nullable();
                $table->string('cliente_nombre', 150);
                $table->string('cliente_documento', 30)->nullable();
                $table->date('fecha');
                $table->decimal('descuento', 12, 2)->default(0);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('impuesto', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);
                $table->string('estado', 20)->default('borrador');
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('detalle_facturas')) {
            Schema::create('detalle_facturas', function (Blueprint $table) {
                $table->id('id_detalle_factura');
                $table->unsignedBigInteger('id_factura');
                $table->unsignedBigInteger('id_producto');
                $table->string('producto_nombre', 150)->nullable();
                $table->decimal('cantidad', 12, 2);
                $table->decimal('precio_unitario', 12, 2);
                $table->decimal('porcentaje_impuesto', 5, 2)->default(0);
                $table->decimal('descuento', 12, 2)->default(0);
                $table->decimal('subtotal', 12, 2);
                $table->timestamps();
            });
        }
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('detalle_facturas');
        Schema::dropIfExists('facturas');
        Schema::dropIfExists('productos');
        Mockery::close();
        parent::tearDown();
    }

    public function test_guardar_usa_impuesto_y_descuento_por_detalle(): void
    {
        Producto::create([
            'id' => 7,
            'nombre' => 'Teclado',
            'descripcion' => 'Periferico',
            'id_categoria' => 1,
            'id_proveedor' => 1,
            'stock_actual' => 10,
            'stock_minimo' => 1,
            'unidad_base' => 'unidad',
            'precio_base' => 25,
            'activo' => true,
            'fecha_registro' => now(),
            'fecha_actualizacion' => now(),
        ]);

        $repo = Mockery::mock(FacturaRepository::class);

        $repo->shouldReceive('guardar')->once()->withArgs(function (array $cabecera) {
            $this->assertSame(100.0, (float) $cabecera['subtotal']);
            $this->assertSame(25.0, (float) $cabecera['descuento']);
            $this->assertSame(7.5, (float) $cabecera['impuesto']);
            $this->assertSame(82.5, (float) $cabecera['total']);
            $this->assertArrayNotHasKey('porcentaje_impuesto', $cabecera);

            return true;
        })->andReturn(new Facturas(['id_factura' => 1]));

        $repo->shouldReceive('reemplazarDetalles')->once()->withArgs(function (Facturas $factura, array $detalles) {
            $this->assertCount(1, $detalles);
            $this->assertSame('Teclado', $detalles[0]['producto_nombre']);
            $this->assertSame(10.0, (float) $detalles[0]['porcentaje_impuesto']);
            $this->assertSame(25.0, (float) $detalles[0]['descuento']);
            $this->assertSame(100.0, (float) $detalles[0]['subtotal']);

            return true;
        });

        $service = new FacturaService($repo);

        $service->guardar([
            'numero' => 'F001',
            'cliente_nombre' => 'Ana',
            'fecha' => '2026-09-22',
            'estado' => 'emitida',
            'observaciones' => 'Test',
            'detalles' => [
                [
                    'id_producto' => 7,
                    'producto_nombre' => 'Teclado',
                    'cantidad' => 4,
                    'precio_unitario' => 25,
                    'porcentaje_impuesto' => 10,
                    'descuento' => 25,
                ],
            ],
        ]);
    }

    public function test_guardar_usa_precio_base_del_producto_si_no_se_envia_precio_manual(): void
    {
        $producto = Producto::create([
            'nombre' => 'Arroz',
            'descripcion' => 'Granos',
            'id_categoria' => 1,
            'id_proveedor' => 1,
            'stock_actual' => 10,
            'stock_minimo' => 1,
            'unidad_base' => 'kg',
            'precio_base' => 3000,
            'activo' => true,
            'fecha_registro' => now(),
            'fecha_actualizacion' => now(),
        ]);

        $repo = Mockery::mock(FacturaRepository::class);

        $repo->shouldReceive('guardar')->once()->withArgs(function (array $cabecera) {
            $this->assertSame(6000.0, (float) $cabecera['subtotal']);
            $this->assertSame(0.0, (float) $cabecera['descuento']);
            $this->assertSame(6000.0, (float) $cabecera['total']);

            return true;
        })->andReturn(new Facturas(['id_factura' => 1]));

        $repo->shouldReceive('reemplazarDetalles')->once()->withArgs(function (Facturas $factura, array $detalles) {
            $this->assertCount(1, $detalles);
            $this->assertSame('Arroz', $detalles[0]['producto_nombre']);
            $this->assertSame(3000.0, (float) $detalles[0]['precio_unitario']);
            $this->assertSame(6000.0, (float) $detalles[0]['subtotal']);

            return true;
        });

        $service = new FacturaService($repo);

        $service->guardar([
            'numero' => 'F002',
            'cliente_nombre' => 'Luis',
            'fecha' => '2026-09-22',
            'estado' => 'borrador',
            'detalles' => [
                [
                    'id_producto' => $producto->id,
                    'cantidad' => 2,
                    'porcentaje_impuesto' => 0,
                    'descuento' => 0,
                ],
            ],
        ]);
    }

    public function test_guardar_genera_numero_automatico_si_no_llega_en_los_datos(): void
    {
        $repo = Mockery::mock(FacturaRepository::class);

        $repo->shouldReceive('guardar')->once()->withArgs(function (array $cabecera) {
            $this->assertMatchesRegularExpression('/^FAC-\d{14}-\d{4,}$/', $cabecera['numero']);

            return true;
        })->andReturn(new Facturas(['id_factura' => 1]));

        $repo->shouldReceive('reemplazarDetalles')->once();

        $service = new FacturaService($repo);

        $service->guardar([
            'cliente_nombre' => 'Maria',
            'fecha' => '2026-09-22',
            'estado' => 'borrador',
            'detalles' => [
                [
                    'id_producto' => 1,
                    'cantidad' => 1,
                    'porcentaje_impuesto' => 0,
                    'descuento' => 0,
                ],
            ],
        ]);
    }

    public function test_reemplazarDetalles_ignora_columnas_que_no_existen_en_la_tabla(): void
    {
        Schema::dropIfExists('detalle_facturas');
        Schema::create('detalle_facturas', function (Blueprint $table) {
            $table->id('id_detalle_factura');
            $table->unsignedBigInteger('id_factura');
            $table->unsignedBigInteger('id_producto');
            $table->decimal('cantidad', 12, 2);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        $factura = new Facturas([
            'cliente_nombre' => 'Pedro',
            'fecha' => '2026-09-22',
            'estado' => 'borrador',
            'numero' => 'FAC-TEST-001',
            'subtotal' => 0,
            'impuesto' => 0,
            'total' => 0,
            'descuento' => 0,
        ]);
        $factura->save();

        $repo = new \App\Repositories\FacturaRepository();

        $repo->reemplazarDetalles($factura, [[
            'id_producto' => 3,
            'producto_nombre' => 'rondalla',
            'cantidad' => 1,
            'precio_unitario' => 2000,
            'porcentaje_impuesto' => 0,
            'descuento' => 0,
            'subtotal' => 2000,
        ]]);

        $this->assertDatabaseHas('detalle_facturas', [
            'id_factura' => $factura->id_factura,
            'id_producto' => 3,
            'precio_unitario' => '2000.00',
        ]);
    }
}

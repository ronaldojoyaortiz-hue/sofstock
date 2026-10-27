<?php

namespace Tests\Feature;

use App\Models\Producto;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductoCamposTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('categorias', function (Blueprint $table) {
            $table->id('id_categoria');
            $table->string('nombre');
            $table->boolean('activa')->default(true);
        });

        Schema::create('proveedores', function (Blueprint $table) {
            $table->id('id_proveedor');
            $table->string('razon_social');
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('marca', 100)->nullable();
            $table->unsignedBigInteger('id_categoria');
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

    protected function tearDown(): void
    {
        Schema::dropIfExists('productos');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('categorias');

        parent::tearDown();
    }

    public function test_crear_y_listar_producto_con_marca_y_estado(): void
    {
        $categoriaId = $this->crearCategoria();

        $this->get(route('productos.create'))
            ->assertOk()
            ->assertDontSee('Precio compra');

        $this->post(route('productos.store'), [
            'nombre' => 'Teclado',
            'descripcion' => 'Teclado mecánico',
            'marca' => 'SofTech',
            'id_categoria' => $categoriaId,
            'stock_actual' => 8,
            'precio_base' => 120,
            'precio_compra' => 75,
            'activo' => 1,
        ])->assertRedirect(route('productos.index'))
            ->assertSessionHasNoErrors();

        $producto = Producto::firstOrFail();
        $this->assertSame('SofTech', $producto->marca);
        $this->assertArrayNotHasKey('precio_compra', $producto->getAttributes());
        $this->assertTrue($producto->activo);

        $this->get(route('productos.index'))
            ->assertOk()
            ->assertSee('ID')
            ->assertSee('Descripción')
            ->assertDontSee('Precio compra')
            ->assertSee('Stock')
            ->assertSee('Marca')
            ->assertSee('Estado')
            ->assertSee('SofTech')
            ->assertSee('Activo');
    }

    public function test_editar_producto_puede_actualizar_marca_y_desactivarlo(): void
    {
        $categoriaId = $this->crearCategoria();
        $producto = Producto::create([
            'nombre' => 'Mouse',
            'id_categoria' => $categoriaId,
            'stock_actual' => 3,
            'precio_base' => 50,
            'activo' => true,
        ]);

        $this->put(route('productos.update', $producto->id), [
            'nombre' => 'Mouse inalámbrico',
            'descripcion' => 'Mouse actualizado',
            'marca' => 'SofTech',
            'id_categoria' => $categoriaId,
            'stock_actual' => 5,
            'precio_base' => 60,
            'activo' => 0,
        ])->assertRedirect(route('productos.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'marca' => 'SofTech',
            'activo' => 0,
        ]);

        $this->get(route('productos.edit', $producto->id))
            ->assertOk()
            ->assertDontSee('Precio compra');
    }

    private function crearCategoria(): int
    {
        return (int) \Illuminate\Support\Facades\DB::table('categorias')->insertGetId([
            'nombre' => 'Accesorios',
            'activa' => true,
        ], 'id_categoria');
    }
}

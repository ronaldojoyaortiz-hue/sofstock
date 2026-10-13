<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FacturaPantallasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('precio_base', 12, 2)->default(0);
        });

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

    protected function tearDown(): void
    {
        Schema::dropIfExists('detalle_facturas');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('facturas');

        parent::tearDown();
    }

    public function test_detalle_de_factura_muestra_edicion_y_eliminacion(): void
    {
        \DB::table('facturas')->insert([
            'numero' => 'FAC-20261006-4198',
            'cliente_nombre' => 'Ronaldo Joya',
            'cliente_documento' => '123456',
            'fecha' => '2026-10-06',
            'descuento' => 0,
            'subtotal' => 26000,
            'impuesto' => 0,
            'total' => 26000,
            'estado' => 'pagada',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('detallefacturas.index'));

        $response->assertOk()
            ->assertSee('FAC-20261006-4198')
            ->assertSee('Ronaldo Joya')
            ->assertSee('06/10/2026')
            ->assertSee('pagada')
            ->assertSee('$26.000,00')
            ->assertSee('/detallefacturas/1/edit')
            ->assertSee('/detallefacturas/1')
            ->assertSee('Editar factura')
            ->assertSee('Eliminar factura');
    }

    public function test_nueva_factura_abre_el_formulario_en_detalle_factura_y_facturas_solo_permite_ver(): void
    {
        \DB::table('facturas')->insert([
            'numero' => 'FAC-20261006-4198',
            'cliente_nombre' => 'Ronaldo Joya',
            'cliente_documento' => '123456',
            'fecha' => '2026-10-06',
            'descuento' => 0,
            'subtotal' => 26000,
            'impuesto' => 0,
            'total' => 26000,
            'estado' => 'pagada',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('facturas.index'))
            ->assertOk()
            ->assertSee(route('detallefacturas.create'))
            ->assertSee('Ver factura', false)
            ->assertDontSee('Editar factura')
            ->assertDontSee('Eliminar factura');

        $this->assertFalse(Route::has('facturas.create'));
        $this->assertFalse(Route::has('facturas.edit'));
        $this->assertFalse(Route::has('facturas.destroy'));
        $this->assertTrue(Route::has('detallefacturas.edit'));
        $this->assertTrue(Route::has('detallefacturas.destroy'));

        $this->get(route('detallefacturas.create'))
            ->assertOk()
            ->assertSee('Nueva factura')
            ->assertSee('Guardar factura');

        $this->get(route('detallefacturas.edit', 1))
            ->assertOk()
            ->assertSee('Editar factura');
    }
}

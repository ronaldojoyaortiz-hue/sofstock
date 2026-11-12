<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureAccountActive;
use App\Http\Middleware\ExigirPreguntaSeguridad;
use App\Http\Middleware\Permiso;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RegistroMermaReporteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            Authenticate::class,
            EnsureAccountActive::class,
            ExigirPreguntaSeguridad::class,
            Permiso::class,
        ]);

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('stock_actual', 12, 2)->default(0);
            $table->decimal('precio_base', 12, 2)->default(0);
        });

        Schema::create('mermas_productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_producto');
            $table->date('fecha');
            $table->decimal('cantidad', 12, 2);
            $table->decimal('valor_perdida', 14, 2);
            $table->string('motivo', 180);
            $table->timestamps();
        });

        Schema::create('auditorias', function (Blueprint $table) {
            $table->id('id_auditoria');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->string('accion');
            $table->string('modulo');
            $table->text('descripcion');
            $table->string('tabla_afectada')->nullable();
            $table->string('id_registro_afectado')->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('motivo')->nullable();
            $table->string('direccion_ip')->nullable();
            $table->text('user_agent')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('auditorias');
        Schema::dropIfExists('mermas_productos');
        Schema::dropIfExists('productos');

        parent::tearDown();
    }

    public function test_merma_calcula_perdida_desde_precio_base_y_descuenta_stock_una_vez(): void
    {
        $productoId = DB::table('productos')->insertGetId([
            'nombre' => 'Café',
            'stock_actual' => 10,
            'precio_base' => 12.50,
        ]);

        $this->post(route('reports.spoilage.store'), [
            'id_producto' => $productoId,
            'cantidad' => 2,
            'fecha' => now()->toDateString(),
            'motivo' => 'Empaque dañado',
            'valor_perdida' => 1,
        ])->assertRedirect(route('reports.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('mermas_productos', [
            'id_producto' => $productoId,
            'cantidad' => 2,
            'valor_perdida' => 25,
            'motivo' => 'Empaque dañado',
        ]);
        $this->assertSame(8.0, (float) DB::table('productos')->where('id', $productoId)->value('stock_actual'));
        $this->assertSame(1, DB::table('mermas_productos')->count());
    }

    public function test_merma_sin_stock_suficiente_no_crea_registro_ni_descontabiliza_stock(): void
    {
        $productoId = DB::table('productos')->insertGetId([
            'nombre' => 'Café',
            'stock_actual' => 1,
            'precio_base' => 12.50,
        ]);

        $this->from(route('reports.index'))
            ->post(route('reports.spoilage.store'), [
                'id_producto' => $productoId,
                'cantidad' => 2,
                'fecha' => now()->toDateString(),
                'motivo' => 'Empaque dañado',
            ])->assertRedirect(route('reports.index'))
            ->assertSessionHasErrors('cantidad');

        $this->assertSame(1.0, (float) DB::table('productos')->where('id', $productoId)->value('stock_actual'));
        $this->assertSame(0, DB::table('mermas_productos')->count());
    }
}

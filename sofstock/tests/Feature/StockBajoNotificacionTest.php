<?php

namespace Tests\Feature;

use App\Services\ProductoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StockBajoNotificacionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('usuarios', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('correo');
            $table->boolean('activo')->default(true);
        });
        Schema::create('productos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->decimal('stock_actual', 12, 2)->default(0);
            $table->decimal('stock_minimo', 12, 2)->default(0);
        });
        Schema::create('producto_alerta_stock_usuario', function (Blueprint $table): void {
            $table->unsignedBigInteger('id_producto');
            $table->unsignedBigInteger('id_usuario');
            $table->timestamps();
            $table->primary(['id_producto', 'id_usuario']);
        });
        Schema::create('notificaciones', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('usuario_emisor_id')->nullable();
            $table->unsignedBigInteger('id_producto')->nullable();
            $table->string('titulo');
            $table->text('mensaje');
            $table->string('tipo');
            $table->dateTime('fecha_evento')->nullable();
            $table->string('lugar')->nullable();
            $table->json('datos')->nullable();
            $table->timestamps();
        });
        Schema::create('notificacion_usuario', function (Blueprint $table): void {
            $table->unsignedBigInteger('id_notificacion');
            $table->unsignedBigInteger('id_usuario');
            $table->dateTime('leida_at')->nullable();
            $table->timestamps();
            $table->primary(['id_notificacion', 'id_usuario']);
        });
        Schema::create('auditorias', function (Blueprint $table): void {
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
            $table->timestamp('created_at')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('auditorias');
        Schema::dropIfExists('notificacion_usuario');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('producto_alerta_stock_usuario');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('usuarios');

        parent::tearDown();
    }

    public function test_alerta_se_envia_a_usuarios_seleccionados_al_cruzar_el_stock_minimo(): void
    {
        $usuarioUno = DB::table('usuarios')->insertGetId([
            'nombre' => 'Ana',
            'apellido' => 'Prueba',
            'correo' => 'ana@example.test',
            'activo' => true,
        ]);
        $usuarioDos = DB::table('usuarios')->insertGetId([
            'nombre' => 'Luis',
            'apellido' => 'Prueba',
            'correo' => 'luis@example.test',
            'activo' => true,
        ]);
        $usuarioTres = DB::table('usuarios')->insertGetId([
            'nombre' => 'Marta',
            'apellido' => 'Prueba',
            'correo' => 'marta@example.test',
            'activo' => true,
        ]);
        $servicio = $this->app->make(ProductoService::class);

        $producto = $servicio->guardar([
            'nombre' => 'Teclado',
            'stock_actual' => 2,
            'stock_minimo' => 5,
            'usuarios_alerta_stock' => [$usuarioUno, $usuarioDos],
        ]);

        $this->assertDatabaseCount('notificaciones', 1);
        $this->assertDatabaseHas('notificaciones', [
            'id_producto' => $producto->id,
            'tipo' => 'stock_bajo',
        ]);
        $this->assertDatabaseHas('notificacion_usuario', [
            'id_notificacion' => 1,
            'id_usuario' => $usuarioUno,
        ]);
        $this->assertDatabaseHas('notificacion_usuario', [
            'id_notificacion' => 1,
            'id_usuario' => $usuarioDos,
        ]);

        $servicio->actualizar($producto->id, [
            'stock_actual' => 3,
            'stock_minimo' => 5,
            'usuarios_alerta_stock' => [$usuarioUno, $usuarioDos, $usuarioTres],
        ]);
        $this->assertDatabaseCount('notificaciones', 2);
        $this->assertDatabaseHas('notificacion_usuario', [
            'id_notificacion' => 2,
            'id_usuario' => $usuarioTres,
        ]);

        $servicio->actualizar($producto->id, [
            'stock_actual' => 8,
            'stock_minimo' => 5,
        ]);
        $servicio->actualizar($producto->id, [
            'stock_actual' => 4,
            'stock_minimo' => 5,
        ]);
        $this->assertDatabaseCount('notificaciones', 3);
    }
}

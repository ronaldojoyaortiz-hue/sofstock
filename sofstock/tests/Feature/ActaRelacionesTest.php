<?php

namespace Tests\Feature;

use App\Http\Controllers\ActaController;
use App\Models\Acta;
use App\Models\Usuario;
use App\Services\ActaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ActaRelacionesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('roles', function (Blueprint $table): void {
            $table->id('id_rol');
            $table->string('nombre_rol');
        });

        Schema::create('usuarios', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('correo')->unique();
            $table->string('contrasena');
            $table->unsignedBigInteger('id_rol')->nullable();
            $table->boolean('activo')->default(true);
        });

        Schema::create('inmuebles', function (Blueprint $table): void {
            $table->id('id_inmueble');
            $table->string('codigo');
            $table->string('tipo_inmueble');
            $table->string('direccion');
            $table->string('ciudad');
        });

        Schema::create('productos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
        });

        Schema::create('actas', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('tipo');
            $table->string('tipo_acta');
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->string('lugar')->nullable();
            $table->string('titulo');
            $table->text('objetivo')->nullable();
            $table->longText('desarrollo')->nullable();
            $table->longText('conclusiones')->nullable();
            $table->string('estado');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('usuario_nombre');
            $table->unsignedBigInteger('rol_id')->nullable();
            $table->string('rol_nombre')->nullable();
            $table->unsignedBigInteger('inmueble_id')->nullable();
            $table->unsignedBigInteger('firmante_1_usuario_id')->nullable();
            $table->unsignedBigInteger('firmante_1_rol_id')->nullable();
            $table->string('firmante_1_rol')->nullable();
            $table->string('firmante_1_nombre')->nullable();
            $table->string('firmante_1_cargo')->nullable();
            $table->unsignedBigInteger('firmante_2_usuario_id')->nullable();
            $table->unsignedBigInteger('firmante_2_rol_id')->nullable();
            $table->string('firmante_2_rol')->nullable();
            $table->string('firmante_2_nombre')->nullable();
            $table->string('firmante_2_cargo')->nullable();
            $table->json('productos');
            $table->json('firmantes')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Auth::logout();

        foreach (['actas', 'productos', 'inmuebles', 'usuarios', 'roles'] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_actas_no_exponen_rutas_de_edicion(): void
    {
        $this->assertFalse(Route::has('actas.edit'));
        $this->assertFalse(Route::has('actas.update'));
        $this->assertTrue(Route::has('actas.estado'));
    }

    public function test_cambiar_estado_solo_actualiza_el_estado_de_un_acta_borrador(): void
    {
        $usuario = $this->crearUsuario();
        $acta = Acta::query()->create([
            'codigo' => 'ACTA-CAMBIO-ESTADO',
            'tipo' => 'asamblea',
            'tipo_acta' => 'asamblea',
            'fecha' => '2026-11-04',
            'titulo' => 'Título que debe conservarse',
            'estado' => 'borrador',
            'usuario_id' => $usuario->id,
            'usuario_nombre' => 'María López',
            'productos' => [],
        ]);

        app(ActaService::class)->cambiarEstado($acta, 'pendiente_aprobacion');

        $actualizada = $acta->fresh();
        $this->assertSame('pendiente_aprobacion', $actualizada->estado);
        $this->assertSame('Título que debe conservarse', $actualizada->titulo);
    }

    public function test_acta_guarda_varios_firmantes_y_productos_en_la_fila_de_actas(): void
    {
        $usuario = $this->crearUsuario();
        $rolSociosId = DB::table('roles')->insertGetId(['nombre_rol' => 'socios']);
        $rolTesoreroId = DB::table('roles')->insertGetId(['nombre_rol' => 'tesorero']);
        $socioUno = $this->crearUsuarioEnRol($rolSociosId, 'Ana', 'Pérez');
        $socioDos = $this->crearUsuarioEnRol($rolTesoreroId, 'Luis', 'Ruiz');
        $productoUno = DB::table('productos')->insertGetId(['nombre' => 'Mesa']);
        $productoDos = DB::table('productos')->insertGetId(['nombre' => 'Silla']);
        Auth::login($usuario);

        $acta = app(ActaService::class)->registrar([
            'codigo' => 'ACTA-001',
            'tipo_acta' => 'entrega',
            'fecha' => '2026-11-04',
            'titulo' => 'Entrega de mobiliario',
            'estado' => 'borrador',
            'roles_firmantes' => [$rolSociosId, $rolTesoreroId],
            'productos' => [
                ['producto_id' => $productoUno, 'cantidad' => 2, 'observaciones' => null],
                ['producto_id' => $productoDos, 'cantidad' => 6, 'observaciones' => 'En buen estado'],
            ],
        ]);

        $this->assertSame('entrega', $acta->tipo_acta);
        $this->assertSame('entrega_bienes', $acta->tipo);
        $this->assertSame('borrador', $acta->estado);
        $this->assertSame(['Ana Pérez', 'Luis Ruiz'], [
            $acta->firmante_1_nombre,
            $acta->firmante_2_nombre,
        ]);
        $this->assertSame([$socioUno->id, $socioDos->id], [
            $acta->firmante_1_usuario_id,
            $acta->firmante_2_usuario_id,
        ]);
        $this->assertCount(2, $acta->firmantes);
        $this->assertSame('tesorero', $acta->firmantes[1]['rol']);
        $this->assertSame(2, count($acta->productos));
        $this->assertSame('Silla', $acta->productos[1]['nombre']);
        $this->assertEquals(6, $acta->productos[1]['cantidad']);
        $this->assertDatabaseHas('actas', [
            'id' => $acta->id,
            'firmante_1_nombre' => 'Ana Pérez',
            'firmante_2_nombre' => 'Luis Ruiz',
        ]);
    }

    public function test_editar_preserva_los_nombres_legacy_de_firmantes_sin_asignar_usuarios(): void
    {
        $usuario = $this->crearUsuario();
        Auth::login($usuario);

        $acta = Acta::query()->create([
            'codigo' => 'ACTA-LEGACY',
            'tipo' => 'entrega_bienes',
            'tipo_acta' => 'entrega',
            'fecha' => '2026-11-04',
            'titulo' => 'Acta antigua',
            'estado' => 'borrador',
            'usuario_id' => $usuario->id,
            'usuario_nombre' => 'María López',
            'firmante_1_nombre' => 'Nombre antiguo 1',
            'firmante_1_cargo' => 'Cargo antiguo 1',
            'firmante_2_nombre' => 'Nombre antiguo 2',
            'firmante_2_cargo' => 'Cargo antiguo 2',
            'productos' => [],
        ]);

        app(ActaService::class)->actualizar($acta, [
            'codigo' => $acta->codigo,
            'tipo_acta' => 'asamblea',
            'fecha' => '2026-11-05',
            'titulo' => 'Acta antigua editada',
            'estado' => 'aprobada',
            'productos' => [],
        ]);

        $actualizada = $acta->fresh();
        $this->assertSame('asamblea', $actualizada->tipo_acta);
        $this->assertSame('Nombre antiguo 1', $actualizada->firmante_1_nombre);
        $this->assertSame('Cargo antiguo 1', $actualizada->firmante_1_cargo);
        $this->assertNull($actualizada->firmante_1_usuario_id);
        $this->assertCount(2, $actualizada->firmantes);
        $this->assertSame([], $actualizada->productos);
    }

    public function test_acta_de_devolucion_se_vincula_con_el_inmueble(): void
    {
        $usuario = $this->crearUsuario();
        $firmanteUno = $this->crearUsuarioEnRol($usuario->id_rol, 'Ana', 'Pérez');
        $secretariaRolId = DB::table('roles')->insertGetId(['nombre_rol' => 'secretaria']);
        $firmanteDos = $this->crearUsuarioEnRol($secretariaRolId, 'Luis', 'Ruiz');
        $inmuebleId = DB::table('inmuebles')->insertGetId([
            'codigo' => 'INM-001',
            'tipo_inmueble' => 'Local',
            'direccion' => 'Calle 1',
            'ciudad' => 'Bogotá',
        ], 'id_inmueble');
        Auth::login($usuario);

        $acta = app(ActaService::class)->registrar([
            'codigo' => 'ACTA-002',
            'tipo_acta' => 'devolucion',
            'fecha' => '2026-11-04',
            'titulo' => 'Devolución del local',
            'estado' => 'firmada',
            'inmueble_id' => $inmuebleId,
            'roles_firmantes' => [$firmanteUno->id_rol, $firmanteDos->id_rol],
            'productos' => [],
        ]);

        $this->assertSame('entrega_inmueble', $acta->tipo);
        $this->assertSame($inmuebleId, $acta->inmueble->id_inmueble);
        $this->assertCount(0, $acta->productos);
    }

    public function test_seleccionar_un_rol_agrega_automaticamente_todos_sus_usuarios_activos(): void
    {
        $usuario = $this->crearUsuario();
        $rolSociosId = DB::table('roles')->insertGetId(['nombre_rol' => 'socios']);
        $socioUno = $this->crearUsuarioEnRol($rolSociosId, 'Ana', 'Pérez');
        $socioDos = $this->crearUsuarioEnRol($rolSociosId, 'Luis', 'Ruiz');
        Auth::login($usuario);

        $acta = app(ActaService::class)->registrar([
            'codigo' => 'ACTA-SOCIOS',
            'tipo_acta' => 'asamblea',
            'fecha' => '2026-11-04',
            'titulo' => 'Asamblea de socios',
            'estado' => 'borrador',
            'roles_firmantes' => [$rolSociosId],
            'productos' => [],
        ]);

        $this->assertCount(2, $acta->firmantes);
        $this->assertSame([$socioUno->id, $socioDos->id], array_column($acta->firmantes, 'usuario_id'));
        $this->assertSame(['Ana Pérez', 'Luis Ruiz'], array_column($acta->firmantes, 'nombre'));
        $this->assertSame(['socios', 'socios'], array_column($acta->firmantes, 'cargo'));
    }

    public function test_se_pueden_validar_varios_roles_a_la_vez(): void
    {
        $rolSociosId = DB::table('roles')->insertGetId(['nombre_rol' => 'socios']);
        $rolTesoreroId = DB::table('roles')->insertGetId(['nombre_rol' => 'tesorero']);

        $validated = $this->validarConControlador(Request::create('/actas', 'POST', [
            'codigo' => 'ACTA-ROLES-MULTIPLES',
            'tipo_acta' => 'junta_directiva',
            'fecha' => '2026-11-04',
            'titulo' => 'Junta directiva',
            'estado' => 'borrador',
            'roles_firmantes' => [$rolSociosId, $rolTesoreroId],
        ]));

        $this->assertSame([$rolSociosId, $rolTesoreroId], $validated['roles_firmantes']);
    }

    public function test_se_puede_crear_un_acta_sin_seleccionar_roles_o_firmantes(): void
    {
        $usuario = $this->crearUsuario();
        Auth::login($usuario);

        $validated = $this->validarConControlador(Request::create('/actas', 'POST', [
            'codigo' => 'ACTA-SIN-FIRMANTES',
            'tipo_acta' => 'asamblea',
            'fecha' => '2026-11-04',
            'titulo' => 'Asamblea sin firmantes',
            'estado' => 'borrador',
        ]));

        $acta = app(ActaService::class)->registrar($validated);

        $this->assertSame([], $acta->firmantes);
        $this->assertNull($acta->firmante_1_nombre);
        $this->assertNull($acta->firmante_2_nombre);
    }

    public function test_codigo_de_acta_duplicado_es_rechazado(): void
    {
        $usuario = $this->crearUsuario();
        $rolSociosId = DB::table('roles')->insertGetId(['nombre_rol' => 'socios']);
        $rolTesoreroId = DB::table('roles')->insertGetId(['nombre_rol' => 'tesorero']);
        $firmanteUno = $this->crearUsuarioEnRol($rolSociosId, 'Ana', 'Pérez');
        $firmanteDos = $this->crearUsuarioEnRol($rolTesoreroId, 'Luis', 'Ruiz');
        Auth::login($usuario);

        app(ActaService::class)->registrar([
            'codigo' => 'ACTA-CODIGO-UNICO',
            'tipo_acta' => 'asamblea',
            'fecha' => '2026-11-04',
            'titulo' => 'Primera acta',
            'estado' => 'borrador',
            'roles_firmantes' => [$firmanteUno->id_rol, $firmanteDos->id_rol],
            'productos' => [],
        ]);

        $request = Request::create('/actas', 'POST', [
            'codigo' => 'ACTA-CODIGO-UNICO',
            'tipo_acta' => 'asamblea',
            'fecha' => '2026-11-05',
            'titulo' => 'Segunda acta',
            'estado' => 'borrador',
            'roles_firmantes' => [$firmanteUno->id_rol, $firmanteDos->id_rol],
        ]);

        try {
            $this->validarConControlador($request);
            $this->fail('No se deben aceptar códigos de acta repetidos.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('codigo', $exception->errors());
        }
    }

    public function test_acta_de_recibo_no_se_acepta_como_nuevo_tipo(): void
    {
        $request = Request::create('/actas', 'POST', [
            'codigo' => 'ACTA-RECIBO-RETIRADA',
            'tipo_acta' => 'recibo',
            'fecha' => '2026-11-05',
            'titulo' => 'Acta de recibo',
            'estado' => 'borrador',
        ]);

        try {
            $this->validarConControlador($request);
            $this->fail('El tipo recibo ya no debe aceptarse en nuevas actas.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('tipo_acta', $exception->errors());
        }
    }

    public function test_eliminar_acta_elimina_el_registro_json_completo(): void
    {
        $usuario = $this->crearUsuario();
        $firmanteUno = $this->crearUsuarioEnRol($usuario->id_rol, 'Ana', 'Pérez');
        $secretariaRolId = DB::table('roles')->insertGetId(['nombre_rol' => 'secretaria']);
        $firmanteDos = $this->crearUsuarioEnRol($secretariaRolId, 'Luis', 'Ruiz');
        Auth::login($usuario);

        $acta = app(ActaService::class)->registrar([
            'codigo' => 'ACTA-ELIMINAR',
            'tipo_acta' => 'inventario',
            'fecha' => '2026-11-04',
            'titulo' => 'Inventario',
            'estado' => 'borrador',
            'roles_firmantes' => [$firmanteUno->id_rol, $firmanteDos->id_rol],
            'productos' => [],
        ]);

        app(ActaService::class)->eliminar($acta);

        $this->assertDatabaseMissing('actas', ['id' => $acta->id]);
    }

    private function crearUsuario(): Usuario
    {
        $rolId = DB::table('roles')->insertGetId(['nombre_rol' => 'administrador'], 'id_rol');

        return $this->crearUsuarioEnRol($rolId, 'María', 'López');
    }

    private function crearUsuarioEnRol(int $rolId, string $nombre, string $apellido): Usuario
    {
        return Usuario::withoutEvents(fn () => Usuario::query()->create([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'correo' => uniqid('acta-', true).'@example.test',
            'contrasena' => 'password',
            'id_rol' => $rolId,
            'activo' => true,
        ]));
    }

    private function validarConControlador(Request $request): array
    {
        $controller = app(ActaController::class);
        $method = new \ReflectionMethod($controller, 'validarActa');

        return $method->invoke($controller, $request);
    }
}

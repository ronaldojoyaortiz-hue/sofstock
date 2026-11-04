<?php

namespace Tests\Feature;

use App\Models\Acta;
use App\Models\Usuario;
use App\Services\ActaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->string('lugar')->nullable();
            $table->string('titulo');
            $table->text('objetivo')->nullable();
            $table->longText('desarrollo')->nullable();
            $table->longText('conclusiones')->nullable();
            $table->string('estado');
            $table->unsignedBigInteger('usuario_id');
            $table->string('usuario_nombre');
            $table->unsignedBigInteger('rol_id')->nullable();
            $table->string('rol_nombre')->nullable();
            $table->unsignedBigInteger('inmueble_id')->nullable();
            $table->string('firmante_1_nombre')->nullable();
            $table->string('firmante_1_cargo')->nullable();
            $table->string('firmante_2_nombre')->nullable();
            $table->string('firmante_2_cargo')->nullable();
            $table->timestamps();
        });

        Schema::create('acta_producto', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('acta_id');
            $table->unsignedBigInteger('producto_id');
            $table->decimal('cantidad', 12, 2);
            $table->string('observaciones', 500)->nullable();
            $table->timestamps();
            $table->unique(['acta_id', 'producto_id']);
        });

        Schema::create('acta_firmas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('acta_id');
            $table->unsignedBigInteger('rol_id')->nullable();
            $table->string('rol_nombre');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('nombre');
            $table->string('cargo');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Auth::logout();

        foreach (['acta_firmas', 'acta_producto', 'actas', 'productos', 'inmuebles', 'usuarios', 'roles'] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_acta_guarda_firmantes_y_varios_productos_con_sus_cantidades(): void
    {
        $usuario = $this->crearUsuario();
        $rolSociosId = DB::table('roles')->insertGetId(['nombre_rol' => 'socios']);
        $socioUno = $this->crearUsuarioEnRol($rolSociosId, 'Ana', 'Pérez');
        $socioDos = $this->crearUsuarioEnRol($rolSociosId, 'Luis', 'Ruiz');
        $productoUno = DB::table('productos')->insertGetId(['nombre' => 'Mesa']);
        $productoDos = DB::table('productos')->insertGetId(['nombre' => 'Silla']);
        Auth::login($usuario);

        $acta = app(ActaService::class)->registrar([
            'codigo' => 'ACTA-001',
            'tipo' => 'entrega_bienes',
            'fecha' => '2026-11-04',
            'titulo' => 'Entrega de mobiliario',
            'estado' => 'borrador',
            'rol_firmantes_id' => $rolSociosId,
            'productos' => [
                ['producto_id' => $productoUno, 'cantidad' => 2, 'observaciones' => null],
                ['producto_id' => $productoDos, 'cantidad' => 6, 'observaciones' => 'En buen estado'],
            ],
        ]);

        $this->assertSame('ACTA-001', $acta->codigo);
        $this->assertSame('borrador', $acta->estado);
        $this->assertSame(['Ana Pérez', 'Luis Ruiz'], $acta->firmas->pluck('nombre')->all());
        $this->assertSame([$socioUno->id, $socioDos->id], $acta->firmas->pluck('usuario_id')->all());
        $this->assertSame(2, $acta->productos()->count());
        $this->assertEquals(6, (float) $acta->productos()->whereKey($productoDos)->first()->pivot->cantidad);
    }

    public function test_acta_se_vincula_con_el_inmueble_seleccionado(): void
    {
        $usuario = $this->crearUsuario();
        $inmuebleId = DB::table('inmuebles')->insertGetId([
            'codigo' => 'INM-001',
            'tipo_inmueble' => 'Local',
            'direccion' => 'Calle 1',
            'ciudad' => 'Bogotá',
        ], 'id_inmueble');
        Auth::login($usuario);

        $acta = app(ActaService::class)->registrar([
            'codigo' => 'ACTA-002',
            'tipo' => 'entrega_inmueble',
            'fecha' => '2026-11-04',
            'titulo' => 'Entrega del local',
            'estado' => 'firmada',
            'inmueble_id' => $inmuebleId,
            'rol_firmantes_id' => $usuario->id_rol,
        ]);

        $this->assertSame($inmuebleId, $acta->inmueble->id_inmueble);
        $this->assertCount(0, $acta->productos);
    }

    public function test_presidente_y_secretaria_no_se_usan_como_roles_de_firma_de_actas(): void
    {
        $usuario = $this->crearUsuario();
        Auth::login($usuario);

        foreach (['presidente', 'secretaria'] as $roleName) {
            $roleId = DB::table('roles')->insertGetId(['nombre_rol' => $roleName], 'id_rol');
            $this->crearUsuarioEnRol($roleId, 'Usuario', ucfirst($roleName));

            try {
                app(ActaService::class)->registrar([
                    'codigo' => 'ACTA-' . strtoupper($roleName),
                    'tipo' => 'asamblea',
                    'fecha' => '2026-11-04',
                    'titulo' => 'Reunión',
                    'estado' => 'borrador',
                    'rol_firmantes_id' => $roleId,
                ]);
                $this->fail("El rol {$roleName} no debe estar disponible para firmas de actas.");
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('rol_firmantes_id', $exception->errors());
            }
        }
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
            'correo' => uniqid('acta-', true) . '@example.test',
            'contrasena' => 'password',
            'id_rol' => $rolId,
            'activo' => true,
        ]));
    }
}

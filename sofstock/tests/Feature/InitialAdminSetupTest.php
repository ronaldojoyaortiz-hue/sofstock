<?php

namespace Tests\Feature;

use App\Models\Notificacion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InitialAdminSetupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('permission_roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id('id_rol');
            $table->string('nombre_rol');
            $table->text('descripcion')->nullable();
        });

        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('usuarios', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('correo')->unique();
            $table->string('contrasena');
            $table->text('contrasena_cifrada')->nullable();
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->string('genero')->nullable();
            $table->string('genero_detalle')->nullable();
            $table->string('tipo_documento')->nullable();
            $table->string('documento')->nullable()->unique();
            $table->unsignedBigInteger('id_rol')->nullable();
            $table->boolean('activo')->default(true);
            $table->string('pregunta_seguridad')->nullable();
            $table->string('respuesta_seguridad')->nullable();
            $table->string('estado_aprobacion', 20)->default('aprobado');
            $table->timestamp('fecha_registro')->nullable();
            $table->timestamp('fecha_actualizacion')->nullable();
        });

        Schema::create('notificaciones', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('usuario_emisor_id')->nullable();
            $table->string('titulo', 150);
            $table->text('mensaje');
            $table->string('tipo', 30)->default('manual');
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
            $table->string('descripcion');
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
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Schema::dropIfExists('notificacion_usuario');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('auditorias');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permission_roles');
        Schema::dropIfExists('permissions');

        parent::tearDown();
    }

    public function test_first_account_can_be_created_as_the_only_initial_administrator(): void
    {
        Permission::create(['name' => 'ver dashboard', 'guard_name' => 'web']);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('initial-admin.create'));

        $this->get(route('initial-admin.create'))->assertOk();

        $this->post(route('initial-admin.store'), [
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.test',
            'contrasena' => 'password123',
            'contrasena_confirmation' => 'password123',
        ])->assertRedirect(route('security-question.setup'));

        $usuario = Usuario::where('correo', 'ana@example.test')->firstOrFail();

        $this->assertTrue(Hash::check('password123', $usuario->contrasena));
        $this->assertSame('administrador', mb_strtolower($usuario->rol->nombre_rol));
        $this->assertTrue($usuario->hasRole('administrador'));
        $this->assertTrue($usuario->hasPermissionTo('ver dashboard'));
        $this->assertAuthenticatedAs($usuario);

        $this->get(route('initial-admin.create'))
            ->assertRedirect(route('login'));
        $this->get(route('login'))
            ->assertDontSee(route('initial-admin.create'));
        $this->post(route('initial-admin.store'), [
            'nombre' => 'Otra',
            'apellido' => 'Persona',
            'correo' => 'otra@example.test',
            'contrasena' => 'password123',
            'contrasena_confirmation' => 'password123',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('usuarios', 1);
    }

    public function test_only_an_administrator_can_create_additional_accounts(): void
    {
        $legacyRole = Rol::create(['nombre_rol' => 'empleado']);
        $usuario = Usuario::create([
            'nombre' => 'Eva',
            'apellido' => 'Empleado',
            'correo' => 'eva@example.test',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $legacyRole->id_rol,
            'activo' => true,
            'pregunta_seguridad' => 'Pregunta de prueba',
            'respuesta_seguridad' => Hash::make('respuesta'),
        ]);
        $permission = Permission::create(['name' => 'crear usuarios', 'guard_name' => 'web']);
        $employeeRole = Role::create(['name' => 'empleado', 'guard_name' => 'web']);
        $employeeRole->givePermissionTo($permission);
        $usuario->assignRole($employeeRole);

        $this->actingAs($usuario)
            ->get(route('usuarios.create'))
            ->assertForbidden();

        $this->actingAs($usuario)
            ->post(route('usuarios.store'), [
                'nombre' => 'Nueva',
                'apellido' => 'Cuenta',
                'correo' => 'nueva@example.test',
                'contrasena' => 'password123',
                'contrasena_confirmation' => 'password123',
                'id_rol' => $legacyRole->id_rol,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('usuarios', ['correo' => 'nueva@example.test']);
    }

    public function test_public_registration_waits_for_admin_approval_and_cannot_create_another_admin(): void
    {
        Permission::create(['name' => 'ver dashboard', 'guard_name' => 'web']);

        $this->get(route('register'))
            ->assertRedirect(route('initial-admin.create'));

        $this->post(route('initial-admin.store'), [
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.test',
            'contrasena' => 'password123',
            'contrasena_confirmation' => 'password123',
        ])->assertRedirect(route('security-question.setup'));

        $administrador = Usuario::where('correo', 'ana@example.test')->firstOrFail();
        $administrador->forceFill([
            'pregunta_seguridad' => 'Pregunta de prueba',
            'respuesta_seguridad' => Hash::make('respuesta'),
        ])->save();
        $rolEmpleado = Rol::create(['nombre_rol' => 'empleado']);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('name="telefono"', false)
            ->assertSee('name="direccion"', false)
            ->assertSee('name="genero"', false)
            ->assertSee('name="tipo_documento"', false)
            ->assertSee('name="documento"', false)
            ->assertSee('¿Cuál?')
            ->assertSee('genero-detalle-contenedor')
            ->assertSee('name="genero_detalle"', false);
        $this->from(route('register.store'))->post(route('register.store'), [
            'nombre' => 'Eva',
            'apellido' => 'Empleado',
            'correo' => 'eva@example.test',
            'genero' => 'Otro',
            'contrasena' => 'password456',
            'contrasena_confirmation' => 'password456',
        ])->assertSessionHasErrors('genero_detalle');
        $this->post(route('register.store'), [
            'nombre' => 'Eva',
            'apellido' => 'Empleado',
            'correo' => 'eva@example.test',
            'telefono' => '3001234567',
            'direccion' => 'Calle 10',
            'genero' => 'Femenino',
            'genero_detalle' => 'No debe guardarse',
            'tipo_documento' => 'CC',
            'documento' => '123456789',
            'contrasena' => 'password456',
            'contrasena_confirmation' => 'password456',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->post(route('register.store'), [
            'nombre' => 'Otra',
            'apellido' => 'Persona',
            'correo' => 'otra@example.test',
            'genero' => 'Otro',
            'genero_detalle' => 'No binario',
            'contrasena' => 'password789',
            'contrasena_confirmation' => 'password789',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $solicitud = Usuario::where('correo', 'eva@example.test')->firstOrFail();
        $this->assertSame('3001234567', $solicitud->telefono);
        $this->assertSame('Calle 10', $solicitud->direccion);
        $this->assertSame('Femenino', $solicitud->genero);
        $this->assertNull($solicitud->genero_detalle);
        $this->assertSame('CC', $solicitud->tipo_documento);
        $this->assertSame('123456789', $solicitud->documento);
        $this->assertSame('password456', Crypt::decryptString($solicitud->contrasena_cifrada));
        $this->assertSame(
            'No binario',
            Usuario::where('correo', 'otra@example.test')->firstOrFail()->genero_detalle
        );
        $this->assertFalse($solicitud->activo);
        $this->assertSame('pendiente', $solicitud->estado_aprobacion);
        $notificacion = Notificacion::where('tipo', Notificacion::TIPO_SOLICITUD_REGISTRO)->firstOrFail();
        $this->assertDatabaseHas('notificacion_usuario', [
            'id_notificacion' => $notificacion->id,
            'id_usuario' => $administrador->id,
        ]);

        $solicitud->forceFill(['activo' => true])->save();
        $this->post(route('login.authenticate'), [
            'correo' => $solicitud->correo,
            'contrasena' => 'password456',
        ])->assertSessionHasErrors('correo');
        $solicitud->forceFill(['activo' => false])->save();

        $this->actingAs($administrador);
        $this->get(route('registration-requests.index'))->assertOk();
        $this->from(route('registration-requests.index'))
            ->post(route('registration-requests.approve', $solicitud), [
                'id_rol' => $administrador->id_rol,
            ])->assertSessionHasErrors('id_rol');
        $this->assertSame('pendiente', $solicitud->fresh()->estado_aprobacion);

        $this->post(route('registration-requests.approve', $solicitud), [
            'id_rol' => $rolEmpleado->id_rol,
        ])->assertRedirect(route('registration-requests.index'))
            ->assertSessionHas('success');

        $solicitud->refresh();
        $this->assertTrue($solicitud->activo);
        $this->assertSame('aprobado', $solicitud->estado_aprobacion);
        $this->assertSame($rolEmpleado->id_rol, $solicitud->id_rol);
        $this->assertSame(1, Usuario::administradores()->count());

        $this->get(route('usuarios.show', $solicitud))
            ->assertOk()
            ->assertSee('value="password456"', false)
            ->assertSee('3001234567')
            ->assertSee('Calle 10')
            ->assertSee('Femenino')
            ->assertSee('CC')
            ->assertSee('123456789')
            ->assertDontSee('Última actualización');

        Rol::create(['nombre_rol' => 'secretaria']);
        $this->get(route('usuarios.create'))->assertDontSee('secretaria');
        $this->get(route('roles.index'))->assertDontSee('secretaria');
    }
}

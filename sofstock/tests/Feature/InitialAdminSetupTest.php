<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
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
            $table->unsignedBigInteger('id_rol')->nullable();
            $table->boolean('activo')->default(true);
            $table->string('pregunta_seguridad')->nullable();
            $table->string('respuesta_seguridad')->nullable();
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
}

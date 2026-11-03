<?php

namespace Tests\Feature;

use App\Models\Notificacion;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NotificacionEliminarPermisoTest extends TestCase
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

        Schema::create('roles', function (Blueprint $table): void {
            $table->id('id_rol');
            $table->string('nombre_rol');
            $table->text('descripcion')->nullable();
        });

        Schema::create('usuarios', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('correo');
            $table->string('contrasena');
            $table->unsignedBigInteger('id_rol')->nullable();
            $table->boolean('activo')->nullable();
        });

        Schema::create('notificaciones', function (Blueprint $table): void {
            $table->id();
            $table->string('titulo');
            $table->text('mensaje');
            $table->string('tipo');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permission_roles');
        Schema::dropIfExists('permissions');

        parent::tearDown();
    }

    public function test_user_without_delete_permission_cannot_delete_a_notification(): void
    {
        $usuario = $this->crearUsuario();
        $notificacion = $this->crearNotificacion();

        $this->actingAs($usuario)
            ->delete(route('notificaciones.destroy', $notificacion))
            ->assertForbidden();

        $this->assertDatabaseHas('notificaciones', ['id' => $notificacion->id]);
    }

    public function test_user_with_delete_permission_can_delete_a_notification(): void
    {
        $role = Role::create(['name' => 'socios', 'guard_name' => 'web']);
        $permission = Permission::create(['name' => 'eliminar notificaciones', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $usuario = $this->crearUsuario();
        $usuario->assignRole($role);
        $notificacion = $this->crearNotificacion();

        $this->actingAs($usuario)
            ->delete(route('notificaciones.destroy', $notificacion))
            ->assertRedirect(route('notificaciones.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('notificaciones', ['id' => $notificacion->id]);
    }

    private function crearUsuario(): Usuario
    {
        $legacyRoleId = 1;
        DB::table('roles')->insert([
            'nombre_rol' => 'socios',
            'descripcion' => 'Rol de prueba',
        ]);

        return Usuario::withoutEvents(fn () => Usuario::query()->create([
            'nombre' => 'Usuario',
            'apellido' => 'Prueba',
            'correo' => uniqid('usuario-', true) . '@example.test',
            'contrasena' => 'password',
            'id_rol' => $legacyRoleId,
        ]));
    }

    private function crearNotificacion(): Notificacion
    {
        return Notificacion::query()->create([
            'titulo' => 'Aviso',
            'mensaje' => 'Notificación de prueba',
            'tipo' => Notificacion::TIPO_MANUAL,
        ]);
    }
}

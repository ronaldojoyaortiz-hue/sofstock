<?php

namespace Tests\Feature;

use App\Models\Rol as LegacyRole;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SeguridadPermisosTest extends TestCase
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
            $table->string('correo');
            $table->string('contrasena');
            $table->unsignedBigInteger('id_rol')->nullable();
            $table->boolean('activo')->nullable();
        });

        Schema::create('auditorias', function (Blueprint $table): void {
            $table->id('id_auditoria');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->string('accion')->nullable();
            $table->string('modulo')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('tabla_afectada')->nullable();
            $table->string('id_registro_afectado')->nullable();
            $table->text('datos_anteriores')->nullable();
            $table->text('datos_nuevos')->nullable();
            $table->text('motivo')->nullable();
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

    public function test_administrator_can_assign_any_permission_and_keep_managing_permissions(): void
    {
        $administratorRole = Role::create(['name' => 'administrador', 'guard_name' => 'web']);
        $memberRole = Role::create(['name' => 'socios', 'guard_name' => 'web']);
        $manageSecurity = Permission::create(['name' => 'gestionar seguridad', 'guard_name' => 'web']);
        $customPermission = Permission::create(['name' => 'exportar reportes', 'guard_name' => 'web']);
        $memberRole->givePermissionTo($customPermission);
        $administratorRole->givePermissionTo($manageSecurity);

        $administrator = Usuario::withoutEvents(fn () => Usuario::create([
            'nombre' => 'Admin',
            'apellido' => 'Prueba',
            'correo' => 'admin@example.test',
            'contrasena' => 'password',
        ]));
        $administrator->assignRole($administratorRole);
        $this->actingAs($administrator);

        $this->get(route('seguridad.permisos.index'))
            ->assertOk()
            ->assertSee('Ver información completa del rol socios')
            ->assertSee('Editar permisos de socios')
            ->assertSee('Eliminar rol socios')
            ->assertDontSee('exportar reportes')
            ->assertDontSee('name="permissions[]"');

        $this->get(route('seguridad.permisos.roles.show', $memberRole))
            ->assertOk()
            ->assertSee('ID del rol de seguridad')
            ->assertSee('Permisos asignados')
            ->assertSee('exportar reportes');

        $this->get(route('seguridad.permisos.roles', [
            'role_id' => $memberRole->id,
            'gestionar' => 1,
        ]))
            ->assertOk()
            ->assertSee('exportar reportes');

        $this->put(route('seguridad.permisos.update', $memberRole), [
            'permissions' => [$customPermission->id],
        ])->assertRedirect(route('seguridad.permisos.roles', [
            'role_id' => $memberRole->id,
            'gestionar' => 1,
        ]));

        $this->assertTrue($memberRole->fresh()->hasPermissionTo($customPermission));

        $this->put(route('seguridad.permisos.update', $administratorRole), [
            'permissions' => [$customPermission->id],
        ])->assertRedirect(route('seguridad.permisos.roles', [
            'role_id' => $administratorRole->id,
            'gestionar' => 1,
        ]));

        $this->assertTrue($administratorRole->fresh()->hasPermissionTo($customPermission));
        $this->assertFalse($administratorRole->fresh()->hasPermissionTo($manageSecurity));
        $this->get(route('seguridad.permisos.index'))->assertOk();
    }

    public function test_administrator_can_view_and_remove_a_permission_assignment_for_one_role(): void
    {
        $administratorRole = Role::create(['name' => 'administrador', 'guard_name' => 'web']);
        $memberRole = Role::create(['name' => 'socios', 'guard_name' => 'web']);
        $manageSecurity = Permission::create(['name' => 'gestionar seguridad', 'guard_name' => 'web']);
        $administratorRole->givePermissionTo($manageSecurity);

        $administrator = Usuario::withoutEvents(fn () => Usuario::create([
            'nombre' => 'Admin',
            'apellido' => 'Prueba',
            'correo' => 'admin@example.test',
            'contrasena' => 'password',
        ]));
        $administrator->assignRole($administratorRole);
        $this->actingAs($administrator);

        $permission = Permission::create(['name' => 'ver productos', 'guard_name' => 'web']);
        $memberRole->givePermissionTo($permission);
        $administratorRole->givePermissionTo($permission);

        $this->get(route('seguridad.permisos.assignments'))
            ->assertOk()
            ->assertSee('ID del rol')
            ->assertSee((string) $memberRole->id)
            ->assertSee('ver productos')
            ->assertSee('Quitar permiso del rol');

        $this->get(route('seguridad.permisos.assignment.show', [$memberRole->id, $permission->id]))
            ->assertOk()
            ->assertSee((string) $memberRole->id)
            ->assertSee('socios')
            ->assertSee('ver productos');

        $this->delete(route('seguridad.permisos.assignment.destroy', [$memberRole->id, $permission->id]))
            ->assertRedirect(route('seguridad.permisos.assignments'));
        $this->assertDatabaseHas('permissions', ['id' => $permission->id, 'name' => 'ver productos']);
        $this->assertDatabaseHas('permission_roles', ['id' => $memberRole->id, 'name' => 'socios']);
        $this->assertDatabaseMissing('role_has_permissions', [
            'permission_id' => $permission->id,
            'role_id' => $memberRole->id,
        ]);
        $this->assertDatabaseHas('role_has_permissions', [
            'permission_id' => $permission->id,
            'role_id' => $administratorRole->id,
        ]);
    }

    public function test_role_detail_shows_description_permissions_and_associated_users(): void
    {
        $administratorRole = Role::create(['name' => 'administrador', 'guard_name' => 'web']);
        $memberRole = Role::create(['name' => 'socios', 'guard_name' => 'web']);
        $manageSecurity = Permission::create(['name' => 'gestionar seguridad', 'guard_name' => 'web']);
        $permission = Permission::create(['name' => 'ver productos', 'guard_name' => 'web']);
        $administratorRole->givePermissionTo($manageSecurity);
        $memberRole->givePermissionTo($permission);

        $legacyRole = LegacyRole::withoutEvents(fn () => LegacyRole::create([
            'nombre_rol' => 'socios',
            'descripcion' => 'Acceso de consulta para socios',
        ]));
        $administrator = Usuario::withoutEvents(fn () => Usuario::create([
            'nombre' => 'Admin',
            'apellido' => 'Prueba',
            'correo' => 'admin@example.test',
            'contrasena' => 'password',
        ]));
        $administrator->assignRole($administratorRole);
        $member = Usuario::withoutEvents(fn () => Usuario::create([
            'nombre' => 'Maria',
            'apellido' => 'Socios',
            'correo' => 'socia@example.test',
            'contrasena' => 'password',
            'id_rol' => $legacyRole->id_rol,
            'activo' => true,
        ]));
        $member->assignRole($memberRole);

        $this->actingAs($administrator)
            ->get(route('seguridad.permisos.roles.show', $memberRole))
            ->assertOk()
            ->assertSee('Acceso de consulta para socios')
            ->assertSee('ver productos')
            ->assertSee('Maria Socios')
            ->assertSee('socia@example.test');
    }

    public function test_role_can_be_deleted_when_it_has_no_users_and_admin_role_is_protected(): void
    {
        $administratorRole = Role::create(['name' => 'administrador', 'guard_name' => 'web']);
        $roleToDelete = Role::create(['name' => 'invitado', 'guard_name' => 'web']);
        $manageSecurity = Permission::create(['name' => 'gestionar seguridad', 'guard_name' => 'web']);
        $administratorRole->givePermissionTo($manageSecurity);
        $legacyRole = LegacyRole::withoutEvents(fn () => LegacyRole::create([
            'nombre_rol' => 'invitado',
            'descripcion' => 'Acceso temporal',
        ]));
        $administrator = Usuario::withoutEvents(fn () => Usuario::create([
            'nombre' => 'Admin',
            'apellido' => 'Prueba',
            'correo' => 'admin@example.test',
            'contrasena' => 'password',
        ]));
        $administrator->assignRole($administratorRole);
        $this->actingAs($administrator);

        $this->delete(route('seguridad.permisos.roles.destroy', $administratorRole))
            ->assertRedirect(route('seguridad.permisos.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('permission_roles', ['id' => $administratorRole->id]);

        $this->delete(route('seguridad.permisos.roles.destroy', $roleToDelete))
            ->assertRedirect(route('seguridad.permisos.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('permission_roles', ['id' => $roleToDelete->id]);
        $this->assertDatabaseMissing('roles', ['id_rol' => $legacyRole->id_rol]);
    }

    public function test_role_with_users_cannot_be_deleted(): void
    {
        $administratorRole = Role::create(['name' => 'administrador', 'guard_name' => 'web']);
        $memberRole = Role::create(['name' => 'socios', 'guard_name' => 'web']);
        $manageSecurity = Permission::create(['name' => 'gestionar seguridad', 'guard_name' => 'web']);
        $administratorRole->givePermissionTo($manageSecurity);
        $administrator = Usuario::withoutEvents(fn () => Usuario::create([
            'nombre' => 'Admin',
            'apellido' => 'Prueba',
            'correo' => 'admin@example.test',
            'contrasena' => 'password',
        ]));
        $administrator->assignRole($administratorRole);
        $member = Usuario::withoutEvents(fn () => Usuario::create([
            'nombre' => 'Maria',
            'apellido' => 'Socios',
            'correo' => 'socia@example.test',
            'contrasena' => 'password',
        ]));
        $member->assignRole($memberRole);

        $this->actingAs($administrator)
            ->delete(route('seguridad.permisos.roles.destroy', $memberRole))
            ->assertRedirect(route('seguridad.permisos.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('permission_roles', ['id' => $memberRole->id]);
    }
}

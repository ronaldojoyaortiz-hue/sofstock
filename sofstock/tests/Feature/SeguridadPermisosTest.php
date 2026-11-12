<?php

namespace Tests\Feature;

use App\Models\Rol as LegacyRole;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
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
            $table->boolean('activo')->default(true);
            $table->string('pregunta_seguridad')->nullable();
            $table->string('respuesta_seguridad')->nullable();
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

    public function test_administrator_can_manage_other_roles_but_is_not_available_for_permission_management(): void
    {
        $administratorRole = Role::create(['name' => 'administrador', 'guard_name' => 'web']);
        $memberRole = Role::create(['name' => 'socios', 'guard_name' => 'web']);
        $manageSecurity = Permission::create(['name' => 'gestionar seguridad', 'guard_name' => 'web']);
        $customPermission = Permission::create(['name' => 'exportar reportes', 'guard_name' => 'web']);
        $accessProducts = Permission::create(['name' => 'acceder productos', 'guard_name' => 'web']);
        $readProducts = Permission::create(['name' => 'ver productos', 'guard_name' => 'web']);
        $createProducts = Permission::create(['name' => 'crear productos', 'guard_name' => 'web']);
        $editProducts = Permission::create(['name' => 'editar productos', 'guard_name' => 'web']);
        $deleteProducts = Permission::create(['name' => 'eliminar productos', 'guard_name' => 'web']);
        $readInvoiceDetails = Permission::create(['name' => 'ver detallefacturas', 'guard_name' => 'web']);
        $memberRole->givePermissionTo([
            $customPermission,
            $accessProducts,
            $manageSecurity,
            $readInvoiceDetails,
            $createProducts,
            $editProducts,
            $deleteProducts,
        ]);
        $administratorRole->givePermissionTo($manageSecurity);

        $administrator = Usuario::withoutEvents(fn () => Usuario::create([
            'nombre' => 'Admin',
            'apellido' => 'Prueba',
            'correo' => 'admin@example.test',
            'contrasena' => 'password',
            'pregunta_seguridad' => 'test',
            'respuesta_seguridad' => 'respuesta',
            'activo' => true,
        ]));
        $administrator->assignRole($administratorRole);
        $this->actingAs($administrator);

        $this->get(route('seguridad.permisos.index'))
            ->assertOk()
            ->assertSee('Ver información completa del rol socios')
            ->assertSee('Editar permisos de socios')
            ->assertSee('Eliminar rol socios')
            ->assertDontSee('Ver información completa del rol administrador')
            ->assertDontSee('exportar reportes')
            ->assertDontSee('name="permissions[]"');

        $this->get(route('seguridad.permisos.roles.show', $administratorRole))
            ->assertNotFound();

        $this->get(route('seguridad.permisos.roles.show', $memberRole))
            ->assertOk()
            ->assertSee('ID del rol de seguridad')
            ->assertSee('Permisos asignados')
            ->assertSee('Exportar reportes');

        $this->get(route('seguridad.permisos.roles', [
            'role_id' => $memberRole->id,
            'gestionar' => 1,
        ]))
            ->assertOk()
            ->assertSee('Ver módulo')
            ->assertSee('Leer datos')
            ->assertSee('Crear')
            ->assertSee('Modificar')
            ->assertSee('Eliminar')
            ->assertDontSee('Seleccionar todos los permisos')
            ->assertDontSee('Seleccionar todos de Productos')
            ->assertDontSee('Seleccionar todos de Configuración')
            ->assertSee('Detalles de factura')
            ->assertDontSee('Seleccionar todos de Detalles de factura')
            ->assertSee('value="'.$readInvoiceDetails->id.'"', false)
            ->assertSee('Exportar reportes')
            ->assertDontSee('gestionar seguridad')
            ->assertDontSee('Seguridad')
            ->assertSee('name="permissions[]"', false)
            ->assertSee('value="'.$accessProducts->id.'"', false)
            ->assertSee('value="'.$readProducts->id.'"', false);

        $this->put(route('seguridad.permisos.update', $memberRole), [
            'permissions' => [$customPermission->id, $readProducts->id],
        ])->assertRedirect(route('seguridad.permisos.roles', [
            'role_id' => $memberRole->id,
            'gestionar' => 1,
        ]));

        $this->assertTrue($memberRole->fresh()->hasPermissionTo($customPermission));
        $this->assertTrue($memberRole->fresh()->hasPermissionTo($readProducts));
        $this->assertFalse($memberRole->fresh()->hasPermissionTo($accessProducts));
        $this->assertFalse($memberRole->fresh()->hasPermissionTo($manageSecurity));

        $this->put(route('seguridad.permisos.update', $administratorRole), [
            'permissions' => [$customPermission->id],
        ])->assertRedirect(route('seguridad.permisos.roles'))
            ->assertSessionHas('error');

        $this->assertFalse($administratorRole->fresh()->hasPermissionTo($customPermission));
        $this->assertTrue($administratorRole->fresh()->hasPermissionTo($manageSecurity));
        $this->get(route('seguridad.permisos.index'))->assertOk();
    }

    public function test_new_legacy_roles_are_synchronized_and_displayed_in_permission_management(): void
    {
        $administratorRole = Role::create(['name' => 'administrador', 'guard_name' => 'web']);
        $manageSecurity = Permission::create(['name' => 'gestionar seguridad', 'guard_name' => 'web']);
        $administratorRole->givePermissionTo($manageSecurity);
        $administrator = Usuario::withoutEvents(fn () => Usuario::create([
            'nombre' => 'Admin',
            'apellido' => 'Prueba',
            'correo' => 'admin@example.test',
            'contrasena' => 'password',
            'pregunta_seguridad' => 'test',
            'respuesta_seguridad' => 'respuesta',
            'activo' => true,
        ]));
        $administrator->assignRole($administratorRole);
        $this->actingAs($administrator);

        LegacyRole::withoutEvents(fn () => LegacyRole::create([
            'nombre_rol' => 'nuevo rol',
            'descripcion' => 'Rol creado recientemente',
        ]));

        $this->get(route('seguridad.permisos.index'))
            ->assertOk()
            ->assertSee('nuevo rol');

        $this->assertDatabaseHas('permission_roles', [
            'name' => 'nuevo rol',
            'guard_name' => 'web',
        ]);
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
            'pregunta_seguridad' => 'test',
            'respuesta_seguridad' => 'respuesta',
            'activo' => true,
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
            ->assertSee('Leer datos de Productos')
            ->assertSee('Quitar permiso del rol');

        $this->get(route('seguridad.permisos.assignment.show', [$memberRole->id, $permission->id]))
            ->assertOk()
            ->assertSee((string) $memberRole->id)
            ->assertSee('socios')
            ->assertSee('Leer datos de Productos');

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
            'pregunta_seguridad' => 'test',
            'respuesta_seguridad' => 'respuesta',
            'activo' => true,
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
            ->assertSee('Leer datos de Productos')
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
            'pregunta_seguridad' => 'test',
            'respuesta_seguridad' => 'respuesta',
            'activo' => true,
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

    public function test_non_administrator_cannot_access_permission_management_even_with_legacy_permission(): void
    {
        $memberRole = Role::create(['name' => 'socios', 'guard_name' => 'web']);
        $manageSecurity = Permission::create(['name' => 'gestionar seguridad', 'guard_name' => 'web']);
        $permission = Permission::create(['name' => 'ver productos', 'guard_name' => 'web']);
        $memberRole->givePermissionTo($manageSecurity);
        $memberRole->givePermissionTo($permission);

        $member = Usuario::withoutEvents(fn () => Usuario::create([
            'nombre' => 'Usuario',
            'apellido' => 'Prueba',
            'correo' => 'usuario@example.test',
            'contrasena' => 'password',
            'pregunta_seguridad' => 'test',
            'respuesta_seguridad' => 'respuesta',
            'activo' => true,
        ]));
        $member->assignRole($memberRole);
        $this->actingAs($member);

        $this->get(route('seguridad.permisos.index'))->assertForbidden();
        $this->get(route('seguridad.permisos.assignments'))->assertForbidden();
        $this->get(route('seguridad.permisos.roles'))->assertForbidden();
        $this->put(route('seguridad.permisos.update', $memberRole), [
            'permissions' => [$permission->id],
        ])->assertForbidden();
        $this->get(route('seguridad.permisos.roles.show', $memberRole))->assertForbidden();
        $this->delete(route('seguridad.permisos.roles.destroy', $memberRole))->assertForbidden();
        $this->get(route('seguridad.permisos.assignment.show', [$memberRole->id, $permission->id]))
            ->assertForbidden();
        $this->delete(route('seguridad.permisos.assignment.destroy', [$memberRole->id, $permission->id]))
            ->assertForbidden();
    }

    public function test_module_navigation_and_search_require_access_and_read_permissions(): void
    {
        $role = Role::create(['name' => 'socios', 'guard_name' => 'web']);
        $role->givePermissionTo([
            Permission::create(['name' => 'acceder productos', 'guard_name' => 'web']),
            Permission::create(['name' => 'acceder facturas', 'guard_name' => 'web']),
        ]);

        $user = Usuario::withoutEvents(fn () => Usuario::create([
            'nombre' => 'Usuario',
            'apellido' => 'Prueba',
            'correo' => 'usuario@example.test',
            'contrasena' => 'password',
            'pregunta_seguridad' => 'test',
            'respuesta_seguridad' => 'respuesta',
            'activo' => true,
        ]));
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('search.index', ['q' => 'productos']))
            ->assertOk()
            ->assertDontSee(route('productos.index'))
            ->assertDontSee('Abrir el módulo de productos');

        $this->get(route('detallefacturas.index'))->assertForbidden();
        $this->get(route('search.index', ['q' => 'detalle factura']))
            ->assertOk()
            ->assertDontSee(route('detallefacturas.index'));

        $role->givePermissionTo([
            Permission::create(['name' => 'acceder detallefacturas', 'guard_name' => 'web']),
            Permission::create(['name' => 'ver detallefacturas', 'guard_name' => 'web']),
        ]);

        $this->get(route('search.index', ['q' => 'detalle factura']))
            ->assertOk()
            ->assertSee(route('detallefacturas.index'));
        $this->assertContains(
            'permiso:detallefacturas',
            Route::getRoutes()->getByName('detallefacturas.index')->getAction('middleware')
        );
    }

    public function test_permission_matrix_hides_actions_not_defined_for_a_module(): void
    {
        $administratorRole = Role::create(['name' => 'administrador', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'socios', 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::create([
            'name' => 'editar settings',
            'guard_name' => 'web',
        ]));

        $administrator = Usuario::withoutEvents(fn () => Usuario::create([
            'nombre' => 'Admin',
            'apellido' => 'Prueba',
            'correo' => 'admin@example.test',
            'contrasena' => 'password',
            'pregunta_seguridad' => 'test',
            'respuesta_seguridad' => 'respuesta',
            'activo' => true,
        ]));
        $administrator->assignRole($administratorRole);

        $response = $this->actingAs($administrator)
            ->get(route('seguridad.permisos.roles', [
                'role_id' => $role->id,
                'gestionar' => 1,
            ]))
            ->assertOk()
            ->assertSee('Configuración');

        preg_match('/<form id="role-permissions-form".*?<\/form>/s', $response->getContent(), $matrix);
        $matrixText = strip_tags($matrix[0] ?? '');
        $this->assertStringContainsString('Modificar', $matrixText);
        $this->assertStringNotContainsString('Crear', $matrixText);
    }

    public function test_audit_delete_permission_can_be_selected_for_a_role(): void
    {
        $administratorRole = Role::create(['name' => 'administrador', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'socios', 'guard_name' => 'web']);
        $deleteAudits = Permission::create([
            'name' => 'eliminar auditorias',
            'guard_name' => 'web',
        ]);

        $administrator = Usuario::withoutEvents(fn () => Usuario::create([
            'nombre' => 'Admin',
            'apellido' => 'Prueba',
            'correo' => 'admin@example.test',
            'contrasena' => 'password',
            'pregunta_seguridad' => 'test',
            'respuesta_seguridad' => 'respuesta',
            'activo' => true,
        ]));
        $administrator->assignRole($administratorRole);

        $response = $this->actingAs($administrator)
            ->get(route('seguridad.permisos.roles', [
                'role_id' => $role->id,
                'gestionar' => 1,
            ]))
            ->assertOk()
            ->assertSee('Auditorías');
        preg_match('/<form id="role-permissions-form".*?<\/form>/s', $response->getContent(), $matrix);
        $this->assertStringContainsString('Eliminar', strip_tags($matrix[0] ?? ''));
        $response->assertSee('value="'.$deleteAudits->id.'"', false);
        $response->assertDontSee('Seleccionar todos los permisos');
        $response->assertDontSee('Seleccionar todos de Auditorías');

        $this->put(route('seguridad.permisos.update', $role), [
            'permissions' => [$deleteAudits->id],
        ])->assertRedirect(route('seguridad.permisos.roles', [
            'role_id' => $role->id,
            'gestionar' => 1,
        ]));

        $this->assertTrue($role->fresh()->hasPermissionTo($deleteAudits));
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
            'pregunta_seguridad' => 'test',
            'respuesta_seguridad' => 'respuesta',
            'activo' => true,
        ]));
        $administrator->assignRole($administratorRole);
        $member = Usuario::withoutEvents(fn () => Usuario::create([
            'nombre' => 'Maria',
            'apellido' => 'Socios',
            'correo' => 'socia@example.test',
            'contrasena' => 'password',
            'activo' => true,
        ]));
        $member->assignRole($memberRole);

        $this->actingAs($administrator)
            ->delete(route('seguridad.permisos.roles.destroy', $memberRole))
            ->assertRedirect(route('seguridad.permisos.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('permission_roles', ['id' => $memberRole->id]);
    }

    public function test_users_without_permissions_cannot_open_settings_notifications_or_generate_reports(): void
    {
        $role = Role::create(['name' => 'socios', 'guard_name' => 'web']);
        $user = Usuario::withoutEvents(fn () => Usuario::create([
            'nombre' => 'Maria',
            'apellido' => 'Socios',
            'correo' => 'socia@example.test',
            'contrasena' => 'password',
            'pregunta_seguridad' => 'test',
            'respuesta_seguridad' => 'respuesta',
            'activo' => true,
        ]));
        $user->assignRole($role);
        $role->givePermissionTo(Permission::create([
            'name' => 'acceder settings',
            'guard_name' => 'web',
        ]));

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertForbidden();

        $this->get(route('notificaciones.index'))
            ->assertForbidden();

        $this->post(route('reports.generate'))
            ->assertForbidden();

        $this->get(route('settings.index'))->assertForbidden();

        $role->givePermissionTo(Permission::create([
            'name' => 'ver settings',
            'guard_name' => 'web',
        ]));
        $this->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Mi cuenta')
            ->assertSee(route('account.show'))
            ->assertDontSee('Cambiar contraseña');
        $this->get(route('account.show'))
            ->assertOk()
            ->assertSee('Cambiar contraseña')
            ->assertSee(route('password.change'));

        $role->givePermissionTo(Permission::create([
            'name' => 'editar settings',
            'guard_name' => 'web',
        ]));
        $this->put(route('settings.update'), [
            'nombre_empresa' => 'Empresa',
            'idioma' => 'es',
            'tamano_letra' => 16,
        ])
            ->assertRedirect(route('settings.index'))
            ->assertSessionHas('error');

        $role->givePermissionTo([
            Permission::create(['name' => 'acceder usuarios', 'guard_name' => 'web']),
            Permission::create(['name' => 'editar usuarios', 'guard_name' => 'web']),
        ]);
        $this->get(route('usuarios.edit', $user))->assertForbidden();

        $role->givePermissionTo(Permission::create([
            'name' => 'ver usuarios',
            'guard_name' => 'web',
        ]));
        $this->get(route('usuarios.edit', $user))->assertOk();
    }
}

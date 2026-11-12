<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuditoriaOcultarTest extends TestCase
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
            $table->boolean('activo')->default(true);
            $table->string('pregunta_seguridad')->nullable();
            $table->string('respuesta_seguridad')->nullable();
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

        Schema::create('auditoria_usuario_oculta', function (Blueprint $table): void {
            $table->unsignedBigInteger('id_auditoria');
            $table->unsignedBigInteger('id_usuario');
            $table->timestamp('created_at')->nullable();
            $table->primary(['id_auditoria', 'id_usuario']);
        });
    }

    protected function tearDown(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Schema::dropIfExists('auditoria_usuario_oculta');
        Schema::dropIfExists('auditorias');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permission_roles');
        Schema::dropIfExists('permissions');

        parent::tearDown();
    }

    public function test_deleting_an_audit_hides_it_only_for_the_user_who_deleted_it(): void
    {
        $role = Role::create(['name' => 'auditores', 'guard_name' => 'web']);
        $role->givePermissionTo([
            Permission::create(['name' => 'acceder auditorias', 'guard_name' => 'web']),
            Permission::create(['name' => 'ver auditorias', 'guard_name' => 'web']),
            Permission::create(['name' => 'eliminar auditorias', 'guard_name' => 'web']),
        ]);
        $eliminador = $this->crearUsuario('eliminador@example.test', $role);
        $otroUsuario = $this->crearUsuario('otro@example.test', $role);
        $auditoria = Auditoria::query()->create([
            'id_usuario' => $eliminador->id,
            'accion' => 'CREAR',
            'modulo' => 'Productos',
            'descripcion' => 'Registro para comprobar ocultamiento personal.',
            'created_at' => now(),
        ]);

        $this->actingAs($eliminador)
            ->delete(route('auditorias.destroy', $auditoria->id_auditoria))
            ->assertRedirect(route('auditorias.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('auditorias', ['id_auditoria' => $auditoria->id_auditoria]);
        $this->assertDatabaseHas('auditoria_usuario_oculta', [
            'id_auditoria' => $auditoria->id_auditoria,
            'id_usuario' => $eliminador->id,
        ]);

        $this->actingAs($eliminador)
            ->get(route('auditorias.show', $auditoria->id_auditoria))
            ->assertNotFound();

        $this->actingAs($otroUsuario)
            ->get(route('auditorias.show', $auditoria->id_auditoria))
            ->assertOk()
            ->assertSee('Registro para comprobar ocultamiento personal.');
    }

    public function test_a_user_without_delete_permission_cannot_hide_audit_for_themselves(): void
    {
        $role = Role::create(['name' => 'consulta', 'guard_name' => 'web']);
        $role->givePermissionTo([
            Permission::create(['name' => 'acceder auditorias', 'guard_name' => 'web']),
            Permission::create(['name' => 'ver auditorias', 'guard_name' => 'web']),
        ]);
        $user = $this->crearUsuario('consulta@example.test', $role);
        $auditoria = Auditoria::query()->create([
            'accion' => 'CREAR',
            'modulo' => 'Productos',
            'descripcion' => 'No debe ocultarse.',
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->delete(route('auditorias.destroy', $auditoria->id_auditoria))
            ->assertForbidden();

        $this->assertDatabaseMissing('auditoria_usuario_oculta', [
            'id_auditoria' => $auditoria->id_auditoria,
            'id_usuario' => $user->id,
        ]);
    }

    public function test_user_can_select_and_hide_multiple_audits_from_their_history(): void
    {
        $role = Role::create(['name' => 'auditores', 'guard_name' => 'web']);
        $role->givePermissionTo([
            Permission::create(['name' => 'acceder auditorias', 'guard_name' => 'web']),
            Permission::create(['name' => 'ver auditorias', 'guard_name' => 'web']),
            Permission::create(['name' => 'eliminar auditorias', 'guard_name' => 'web']),
        ]);
        $user = $this->crearUsuario('seleccion@example.test', $role);
        $otroUsuario = $this->crearUsuario('testigo@example.test', $role);
        $auditorias = collect(['Primera auditoría.', 'Segunda auditoría.'])
            ->map(fn (string $descripcion): Auditoria => Auditoria::query()->create([
                'accion' => 'CREAR',
                'modulo' => 'Productos',
                'descripcion' => $descripcion,
                'created_at' => now(),
            ]));

        $this->actingAs($user)
            ->get(route('auditorias.index'))
            ->assertOk()
            ->assertSee('Seleccionar todas las auditorías (todas las páginas)')
            ->assertSee('Eliminar')
            ->assertSee('¿Deseas eliminar las auditorías seleccionadas de tu historial?', false);

        $this->actingAs($user)
            ->delete(route('auditorias.destroy-selected'), [
                'auditorias' => $auditorias->pluck('id_auditoria')->all(),
            ])
            ->assertRedirect(route('auditorias.index'))
            ->assertSessionHas('success');

        foreach ($auditorias as $auditoria) {
            $this->assertDatabaseHas('auditoria_usuario_oculta', [
                'id_auditoria' => $auditoria->id_auditoria,
                'id_usuario' => $user->id,
            ]);
            $this->assertDatabaseHas('auditorias', [
                'id_auditoria' => $auditoria->id_auditoria,
            ]);
        }

        $this->actingAs($otroUsuario)
            ->get(route('auditorias.index'))
            ->assertSee('Primera auditoría.')
            ->assertSee('Segunda auditoría.');
    }

    public function test_select_all_hides_audits_across_all_pages(): void
    {
        $role = Role::create(['name' => 'auditores', 'guard_name' => 'web']);
        $role->givePermissionTo([
            Permission::create(['name' => 'acceder auditorias', 'guard_name' => 'web']),
            Permission::create(['name' => 'ver auditorias', 'guard_name' => 'web']),
            Permission::create(['name' => 'eliminar auditorias', 'guard_name' => 'web']),
        ]);
        $user = $this->crearUsuario('seleccion-todas@example.test', $role);
        $otroUsuario = $this->crearUsuario('testigo-todas@example.test', $role);
        $auditorias = collect(range(1, 31))
            ->map(fn (int $numero): Auditoria => Auditoria::query()->create([
                'accion' => 'CREAR',
                'modulo' => 'Productos',
                'descripcion' => "Auditoría {$numero}.",
                'created_at' => now(),
            ]));

        $this->actingAs($user)
            ->delete(route('auditorias.destroy-selected'), [
                'seleccionar_todas' => '1',
            ])
            ->assertRedirect(route('auditorias.index'))
            ->assertSessionHas('success', '31 auditoría(s) se ocultaron de tu historial. Los demás usuarios todavía podrán verlas.');

        $this->assertDatabaseCount('auditoria_usuario_oculta', 31);
        $this->assertDatabaseHas('auditoria_usuario_oculta', [
            'id_auditoria' => $auditorias->last()->id_auditoria,
            'id_usuario' => $user->id,
        ]);
        $this->assertDatabaseHas('auditorias', [
            'id_auditoria' => $auditorias->last()->id_auditoria,
        ]);

        $this->actingAs($otroUsuario)
            ->get(route('auditorias.show', $auditorias->last()->id_auditoria))
            ->assertOk()
            ->assertSee('Auditoría 31.');
    }

    public function test_bulk_delete_audit_is_recorded_as_eliminar_todo(): void
    {
        $auditoria = app(AuditoriaService::class)->registrar(
            accion: 'ELIMINAR_TODO',
            modulo: 'Productos',
            descripcion: 'Se eliminó todo del módulo Productos.',
            idUsuario: 42,
            eliminarTodo: true
        );

        $this->assertSame('ELIMINAR_TODO', $auditoria->accion);
        $this->assertStringContainsString('eliminar todo', strtolower($auditoria->descripcion));
        $this->assertDatabaseHas('auditorias', [
            'id_auditoria' => $auditoria->id_auditoria,
            'accion' => 'ELIMINAR_TODO',
        ]);
    }

    private function crearUsuario(string $correo, Role $role): Usuario
    {
        $user = Usuario::withoutEvents(fn () => Usuario::query()->create([
            'nombre' => 'Usuario',
            'apellido' => 'Prueba',
            'correo' => $correo,
            'contrasena' => 'password',
            'activo' => true,
            'pregunta_seguridad' => 'Pregunta',
            'respuesta_seguridad' => 'Respuesta',
        ]));
        $user->assignRole($role);

        return $user;
    }
}

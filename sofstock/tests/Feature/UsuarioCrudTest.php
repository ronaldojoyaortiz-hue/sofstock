<?php

namespace Tests\Feature;

use App\Models\Notificacion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsuarioCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_crud_pages_are_available(): void
    {
        $this->get(route('usuarios.index'))->assertOk();
        $this->get(route('usuarios.create'))
            ->assertOk()
            ->assertSee('Género')
            ->assertSee('Masculino')
            ->assertSee('Femenino')
            ->assertSee('No binario')
            ->assertSee('Prefiero no responder')
            ->assertSee('¿Cómo te identificas? (opcional)')
            ->assertSee('Mostrar contraseña')
            ->assertSee('Tarjeta de identidad')
            ->assertSee('Cédula de ciudadanía')
            ->assertSee('Cédula de extranjería')
            ->assertSee('Pasaporte');
    }

    public function test_usuario_details_page_shows_user_information_without_password(): void
    {
        $rol = Rol::create(['nombre_rol' => 'Administrador']);
        $usuario = Usuario::create([
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'telefono' => '3001234567',
            'direccion' => 'Calle 10',
            'tipo_documento' => 'Cédula de ciudadanía',
            'documento' => '123456789',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $rol->id_rol,
            'activo' => true,
        ]);

        $this->get(route('usuarios.show', $usuario))
            ->assertOk()
            ->assertSee('Ana Lopez')
            ->assertSee('ana@example.com')
            ->assertSee('Administrador')
            ->assertSee('3001234567')
            ->assertSee('Calle 10')
            ->assertSee('Cédula de ciudadanía')
            ->assertSee('123456789')
            ->assertSee('Teléfono')
            ->assertSee('Tipo de documento')
            ->assertSee('Contraseña protegida')
            ->assertSee('Restablecer contraseña')
            ->assertDontSee($usuario->contrasena);
    }

    public function test_usuario_details_page_can_reveal_encrypted_password(): void
    {
        $rol = Rol::create(['nombre_rol' => 'Administrador']);
        $password = 'clave-segura-123';
        $usuario = Usuario::create([
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'contrasena' => Hash::make($password),
            'contrasena_cifrada' => Crypt::encryptString($password),
            'id_rol' => $rol->id_rol,
            'activo' => true,
        ]);

        $this->get(route('usuarios.show', $usuario))
            ->assertOk()
            ->assertSee('value="'.$password.'"', false)
            ->assertDontSee($usuario->contrasena);
    }

    public function test_usuario_edit_page_shows_account_status_without_password_fields(): void
    {
        $rol = Rol::create(['nombre_rol' => 'Administrador']);
        $usuario = Usuario::create([
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'contrasena' => Hash::make('password123'),
            'pregunta_seguridad' => 'Pregunta de prueba',
            'respuesta_seguridad' => Hash::make('Respuesta de prueba'),
            'id_rol' => $rol->id_rol,
            'activo' => true,
        ]);

        $this->actingAs($usuario)
            ->get(route('usuarios.edit', $usuario))
            ->assertOk()
            ->assertSee('Estado de la cuenta')
            ->assertSee('Activo')
            ->assertSee('Inactivo')
            ->assertDontSee('Nueva contraseña')
            ->assertDontSee('Confirmar nueva contraseña');
    }

    public function test_usuario_document_type_must_be_a_supported_option(): void
    {
        $rol = Rol::create(['nombre_rol' => 'Administrador']);

        $this->post(route('usuarios.store'), [
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'tipo_documento' => 'Documento inventado',
            'contrasena' => 'password123',
            'contrasena_confirmation' => 'password123',
            'id_rol' => $rol->id_rol,
        ])->assertSessionHasErrors('tipo_documento');
    }

    public function test_usuario_can_be_created_with_other_gender_and_optional_detail(): void
    {
        $rol = Rol::create(['nombre_rol' => 'Administrador']);

        $this->post(route('usuarios.store'), [
            'nombre' => 'Alex',
            'apellido' => 'Perez',
            'correo' => 'alex@example.com',
            'genero' => 'Otro',
            'genero_detalle' => 'Identidad personal',
            'contrasena' => 'password123',
            'contrasena_confirmation' => 'password123',
            'id_rol' => $rol->id_rol,
        ])->assertRedirect(route('usuarios.index'));

        $usuario = Usuario::where('correo', 'alex@example.com')->firstOrFail();
        $this->assertSame('Otro', $usuario->genero);
        $this->assertSame('Identidad personal', $usuario->genero_detalle);
    }

    public function test_gender_detail_is_not_saved_for_other_gender_choices(): void
    {
        $rol = Rol::create(['nombre_rol' => 'Administrador']);

        $this->post(route('usuarios.store'), [
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'genero' => 'Femenino',
            'genero_detalle' => 'Este dato no debe guardarse',
            'contrasena' => 'password123',
            'contrasena_confirmation' => 'password123',
            'id_rol' => $rol->id_rol,
        ])->assertRedirect(route('usuarios.index'));

        $usuario = Usuario::where('correo', 'ana@example.com')->firstOrFail();
        $this->assertSame('Femenino', $usuario->genero);
        $this->assertNull($usuario->genero_detalle);
    }

    public function test_usuario_can_be_created_with_hashed_password_and_inactive_status(): void
    {
        $rol = Rol::create(['nombre_rol' => 'Administrador']);

        $this->post(route('usuarios.store'), [
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'telefono' => '3001234567',
            'direccion' => 'Calle 10',
            'tipo_documento' => 'Cédula de ciudadanía',
            'documento' => '123456789',
            'contrasena' => 'password123',
            'contrasena_confirmation' => 'password123',
            'id_rol' => $rol->id_rol,
        ])->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('success');

        $usuario = Usuario::where('correo', 'ana@example.com')->firstOrFail();
        $this->assertSame('Ana', $usuario->nombre);
        $this->assertSame('3001234567', $usuario->telefono);
        $this->assertSame('Calle 10', $usuario->direccion);
        $this->assertSame('Cédula de ciudadanía', $usuario->tipo_documento);
        $this->assertSame('123456789', $usuario->documento);
        $this->assertFalse($usuario->activo);
        $this->assertTrue(Hash::check('password123', $usuario->contrasena));
    }

    public function test_usuario_can_be_updated_without_changing_password(): void
    {
        $rol = Rol::create(['nombre_rol' => 'Administrador']);
        $usuario = Usuario::create([
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $rol->id_rol,
            'activo' => true,
        ]);
        $password = $usuario->contrasena;

        $this->put(route('usuarios.update', $usuario), [
            'nombre' => 'Ana Maria',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'telefono' => '3107654321',
            'direccion' => 'Carrera 5',
            'tipo_documento' => 'Pasaporte',
            'documento' => 'AB123456',
            'id_rol' => $rol->id_rol,
            'activo' => '1',
        ])->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('success');

        $usuario->refresh();
        $this->assertSame('Ana Maria', $usuario->nombre);
        $this->assertSame('3107654321', $usuario->telefono);
        $this->assertSame('Carrera 5', $usuario->direccion);
        $this->assertSame('Pasaporte', $usuario->tipo_documento);
        $this->assertSame('AB123456', $usuario->documento);
        $this->assertSame($password, $usuario->contrasena);
        $this->assertNotNull($usuario->fecha_actualizacion);
    }

    public function test_usuario_can_be_deleted(): void
    {
        $rolAdministrador = Rol::create(['nombre_rol' => 'Administrador']);
        $rol = Rol::create(['nombre_rol' => 'Empleado']);
        $administrador = Usuario::create([
            'nombre' => 'Luis',
            'apellido' => 'Admin',
            'correo' => 'luis@example.com',
            'contrasena' => Hash::make('admin12345'),
            'pregunta_seguridad' => 'Pregunta de prueba',
            'respuesta_seguridad' => Hash::make('Respuesta de prueba'),
            'id_rol' => $rolAdministrador->id_rol,
            'activo' => true,
        ]);
        $usuario = Usuario::create([
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $rol->id_rol,
            'activo' => true,
        ]);

        $this->actingAs($administrador)
            ->delete(route('usuarios.destroy', $usuario))
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('usuarios', ['id' => $usuario->id]);
    }

    public function test_usuario_can_deactivate_own_account_and_admins_are_notified(): void
    {
        $rolAdministrador = Rol::create(['nombre_rol' => 'Administrador']);
        $rol = Rol::create(['nombre_rol' => 'Empleado']);
        $administrador = Usuario::create([
            'nombre' => 'Luis',
            'apellido' => 'Admin',
            'correo' => 'luis@example.com',
            'contrasena' => Hash::make('admin12345'),
            'pregunta_seguridad' => 'Pregunta de prueba',
            'respuesta_seguridad' => Hash::make('Respuesta de prueba'),
            'id_rol' => $rolAdministrador->id_rol,
            'activo' => true,
        ]);
        $usuario = Usuario::create([
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'contrasena' => Hash::make('password123'),
            'estado_aprobacion' => 'aprobado',
            'pregunta_seguridad' => 'Pregunta de prueba',
            'respuesta_seguridad' => Hash::make('Respuesta de prueba'),
            'id_rol' => $rol->id_rol,
            'activo' => true,
        ]);

        $this->actingAs($usuario)
            ->get(route('account.show'))
            ->assertOk()
            ->assertSee('Desactivar mi cuenta')
            ->assertSee('Cambiar contraseña')
            ->assertSee('ana@example.com');

        $this->post(route('account.deactivate'), [
            'contrasena_actual' => 'password123',
            'confirmar_desactivacion' => '1',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('usuarios', ['id' => $usuario->id, 'activo' => false]);
        $this->assertGuest();

        $notificacion = Notificacion::where('tipo', Notificacion::TIPO_CUENTA_DESACTIVADA)
            ->firstOrFail();
        $this->assertDatabaseHas('notificacion_usuario', [
            'id_notificacion' => $notificacion->id,
            'id_usuario' => $administrador->id,
        ]);

        $this->post(route('login.authenticate'), [
            'correo' => $usuario->correo,
            'contrasena' => 'password123',
        ])->assertSessionHasErrors([
            'correo' => 'Tu cuenta está desactivada. Contacta al administrador para que la reactive.',
        ]);
    }

    public function test_account_deactivation_requires_the_current_password(): void
    {
        $rol = Rol::create(['nombre_rol' => 'Empleado']);
        $usuario = Usuario::create([
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $rol->id_rol,
            'activo' => true,
        ]);

        $this->actingAs($usuario)
            ->from(route('account.show'))
            ->post(route('account.deactivate'), [
                'contrasena_actual' => 'incorrecta',
                'confirmar_desactivacion' => '1',
            ])
            ->assertRedirect(route('account.show'))
            ->assertSessionHasErrors('contrasena_actual');

        $this->assertDatabaseHas('usuarios', ['id' => $usuario->id, 'activo' => true]);
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_last_administrator_cannot_deactivate_own_account(): void
    {
        $rol = Rol::create(['nombre_rol' => 'Administrador']);
        $usuario = Usuario::create([
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $rol->id_rol,
            'activo' => true,
        ]);

        $this->actingAs($usuario)
            ->from(route('account.show'))
            ->post(route('account.deactivate'), [
                'contrasena_actual' => 'password123',
                'confirmar_desactivacion' => '1',
            ])
            ->assertRedirect(route('account.show'))
            ->assertSessionHasErrors('contrasena_actual');

        $this->assertDatabaseHas('usuarios', ['id' => $usuario->id, 'activo' => true]);
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_administrator_can_deactivate_another_user_and_block_their_existing_session(): void
    {
        $rolAdministrador = Rol::create(['nombre_rol' => 'Administrador']);
        $rolUsuario = Rol::create(['nombre_rol' => 'Empleado']);
        $administrador = Usuario::create([
            'nombre' => 'Luis',
            'apellido' => 'Admin',
            'correo' => 'luis@example.com',
            'contrasena' => Hash::make('admin12345'),
            'pregunta_seguridad' => 'Pregunta de prueba',
            'respuesta_seguridad' => Hash::make('Respuesta de prueba'),
            'id_rol' => $rolAdministrador->id_rol,
            'activo' => true,
        ]);
        $usuario = Usuario::create([
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $rolUsuario->id_rol,
            'activo' => true,
        ]);

        $datosUsuario = [
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'telefono' => '',
            'direccion' => '',
            'genero' => '',
            'genero_detalle' => '',
            'tipo_documento' => '',
            'documento' => '',
            'id_rol' => $rolUsuario->id_rol,
        ];

        $this->actingAs($administrador)
            ->get(route('usuarios.edit', $usuario))
            ->assertOk()
            ->assertSee('Estado de la cuenta')
            ->assertSee('Activo')
            ->assertSee('Inactivo');

        $this->put(route('usuarios.update', $usuario), [
            ...$datosUsuario,
            'activo' => '0',
        ])->assertRedirect(route('usuarios.show', $usuario));

        $this->assertDatabaseHas('usuarios', ['id' => $usuario->id, 'activo' => false]);

        $this->get(route('usuarios.edit', $usuario))
            ->assertOk()
            ->assertSee('Inactivo')
            ->assertDontSee('Nueva contraseña');

        $this->actingAs($usuario->fresh())
            ->get(route('account.show'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('correo');
        $this->assertGuest();

        $this->actingAs($administrador)
            ->put(route('usuarios.update', $usuario), [
                ...$datosUsuario,
                'activo' => '1',
            ])
            ->assertRedirect(route('usuarios.show', $usuario));

        $this->assertDatabaseHas('usuarios', ['id' => $usuario->id, 'activo' => true]);
    }
}

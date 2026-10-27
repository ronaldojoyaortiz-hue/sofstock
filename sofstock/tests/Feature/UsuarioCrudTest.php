<?php

namespace Tests\Feature;

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

    public function test_usuario_edit_page_has_password_visibility_controls(): void
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

        $this->get(route('usuarios.edit', $usuario))
            ->assertOk()
            ->assertSee('Nueva contraseña')
            ->assertSee('Mostrar contraseña');
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
            'contrasena' => '',
            'contrasena_confirmation' => '',
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
        $rol = Rol::create(['nombre_rol' => 'Administrador']);
        $usuario = Usuario::create([
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $rol->id_rol,
            'activo' => true,
        ]);

        $this->delete(route('usuarios.destroy', $usuario))
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('usuarios', ['id' => $usuario->id]);
    }
}

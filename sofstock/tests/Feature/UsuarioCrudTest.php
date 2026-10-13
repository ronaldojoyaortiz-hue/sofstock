<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsuarioCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_crud_pages_are_available(): void
    {
        $this->get(route('usuarios.index'))->assertOk();
        $this->get(route('usuarios.create'))->assertOk();
    }

    public function test_usuario_can_be_created_with_hashed_password_and_inactive_status(): void
    {
        $rol = Rol::create(['nombre_rol' => 'Administrador']);

        $this->post(route('usuarios.store'), [
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'contrasena' => 'password123',
            'contrasena_confirmation' => 'password123',
            'id_rol' => $rol->id_rol,
        ])->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('success');

        $usuario = Usuario::where('correo', 'ana@example.com')->firstOrFail();
        $this->assertSame('Ana', $usuario->nombre);
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
            'contrasena' => '',
            'contrasena_confirmation' => '',
            'id_rol' => $rol->id_rol,
            'activo' => '1',
        ])->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('success');

        $usuario->refresh();
        $this->assertSame('Ana Maria', $usuario->nombre);
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

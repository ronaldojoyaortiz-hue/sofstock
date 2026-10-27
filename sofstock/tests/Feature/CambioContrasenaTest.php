<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CambioContrasenaTest extends TestCase
{
    use RefreshDatabase;

    public function test_cambiar_contrasena_requiere_autenticacion(): void
    {
        $this->get(route('password.change'))
            ->assertRedirect(route('login'));
    }

    public function test_usuario_puede_cambiar_su_contrasena(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($usuario)
            ->post(route('password.update'), [
                'contrasena_actual' => 'password123',
                'contrasena_nueva' => 'nueva-clave-456',
                'contrasena_nueva_confirmation' => 'nueva-clave-456',
            ])
            ->assertRedirect(route('password.change'))
            ->assertSessionHas('success');

        $usuario->refresh();

        $this->assertTrue(Hash::check('nueva-clave-456', $usuario->contrasena));
        $this->assertSame(
            'nueva-clave-456',
            Crypt::decryptString($usuario->contrasena_cifrada)
        );
        $this->assertNotNull($usuario->fecha_actualizacion);
    }

    public function test_contrasena_actual_incorrecta_no_cambia_la_contrasena(): void
    {
        $usuario = $this->crearUsuario();
        $contrasenaOriginal = $usuario->contrasena;

        $this->actingAs($usuario)
            ->from(route('password.change'))
            ->post(route('password.update'), [
                'contrasena_actual' => 'incorrecta',
                'contrasena_nueva' => 'nueva-clave-456',
                'contrasena_nueva_confirmation' => 'nueva-clave-456',
            ])
            ->assertRedirect(route('password.change'))
            ->assertSessionHasErrors('contrasena_actual');

        $this->assertSame($contrasenaOriginal, $usuario->fresh()->contrasena);
    }

    private function crearUsuario(): Usuario
    {
        $rol = Rol::create(['nombre_rol' => 'Administrador']);

        return Usuario::create([
            'nombre' => 'Ana',
            'apellido' => 'Lopez',
            'correo' => 'ana@example.com',
            'contrasena' => Hash::make('password123'),
            'id_rol' => $rol->id_rol,
            'activo' => true,
        ]);
    }
}

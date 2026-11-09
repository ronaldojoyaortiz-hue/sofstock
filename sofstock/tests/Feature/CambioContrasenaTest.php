<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
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

    public function test_al_recuperar_contrasena_usuario_puede_elegir_una_nueva_pregunta(): void
    {
        $usuario = $this->crearUsuario();
        $preguntaAnterior = AuthController::PREGUNTAS_SEGURIDAD[0];
        $preguntaNueva = AuthController::PREGUNTAS_SEGURIDAD[1];
        $usuario->forceFill([
            'pregunta_seguridad' => $preguntaAnterior,
            'respuesta_seguridad' => Hash::make('respuesta anterior'),
        ])->save();

        $this->post(route('password.recovery.question'), ['correo' => $usuario->correo])
            ->assertOk()
            ->assertViewIs('auth.reset-password-security');

        $this->post(route('password.recovery.reset'), [
            'respuesta_seguridad' => 'respuesta anterior',
            'contrasena' => 'nueva-clave-456',
            'contrasena_confirmation' => 'nueva-clave-456',
        ])->assertOk()
            ->assertViewIs('auth.confirm-recovery-answer')
            ->assertSee('name="pregunta_seguridad"', false);

        $this->post(route('password.recovery.save-answer'), [
            'pregunta_seguridad' => $preguntaNueva,
            'respuesta_seguridad' => 'respuesta nueva',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $usuario->refresh();
        $this->assertSame($preguntaNueva, $usuario->pregunta_seguridad);
        $this->assertTrue(Hash::check('respuesta nueva', $usuario->respuesta_seguridad));
        $this->assertTrue(Hash::check('nueva-clave-456', $usuario->contrasena));
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

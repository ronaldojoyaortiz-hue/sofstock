<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RecordarmeInicioSesionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('usuarios', function (Blueprint $table): void {
            $table->id();
            $table->string('correo')->unique();
            $table->string('contrasena');
            $table->boolean('activo')->default(true);
            $table->string('estado_aprobacion')->default('aprobado');
            $table->string('pregunta_seguridad')->nullable();
            $table->string('respuesta_seguridad')->nullable();
            $table->text('contrasena_cifrada')->nullable();
            $table->timestamp('fecha_actualizacion')->nullable();
            $table->string('remember_token')->nullable();
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

        DB::table('usuarios')->insert([
            'correo' => 'ana@example.com',
            'contrasena' => Hash::make('password123'),
            'activo' => true,
            'estado_aprobacion' => 'aprobado',
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('auditorias');
        Schema::dropIfExists('usuarios');

        parent::tearDown();
    }

    public function test_login_with_recordarme_creates_a_persistent_login_token(): void
    {
        $usuario = Usuario::query()->where('correo', 'ana@example.com')->firstOrFail();
        $this->assertSame(Usuario::class, config('auth.providers.users.model'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Recordar')
            ->assertDontSee('Recordar cuenta')
            ->assertDontSee('Mantén tu sesión iniciada en este dispositivo');

        $this->post(route('login.authenticate'), [
            'correo' => $usuario->correo,
            'contrasena' => 'password123',
            'recordarme' => '1',
        ])->assertRedirect(route('security-question.setup'));

        $this->assertAuthenticatedAs($usuario);
        $this->assertNotEmpty($usuario->fresh()->getRememberToken());
    }

    public function test_logout_ends_the_usuario_session(): void
    {
        $usuario = Usuario::query()->where('correo', 'ana@example.com')->firstOrFail();

        $this->actingAs($usuario)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_password_recovery_updates_the_password_in_usuarios(): void
    {
        $usuario = Usuario::query()->where('correo', 'ana@example.com')->firstOrFail();
        $usuario->forceFill([
            'pregunta_seguridad' => AuthController::PREGUNTAS_SEGURIDAD[0],
            'respuesta_seguridad' => Hash::make('respuesta de prueba'),
        ])->save();

        $this->post(route('password.recovery.question'), ['correo' => $usuario->correo])
            ->assertOk();

        $this->post(route('password.recovery.reset'), [
            'respuesta_seguridad' => 'respuesta de prueba',
            'contrasena' => 'nueva-clave-789',
            'contrasena_confirmation' => 'nueva-clave-789',
        ])->assertOk()
            ->assertViewIs('auth.confirm-recovery-answer');

        $this->post(route('password.recovery.save-answer'), [
            'pregunta_seguridad' => AuthController::PREGUNTAS_SEGURIDAD[1],
            'respuesta_seguridad' => 'nueva respuesta',
        ])->assertRedirect(route('login'));

        $usuario->refresh();
        $this->assertTrue(Hash::check('nueva-clave-789', $usuario->contrasena));
        $this->assertSame('nueva-clave-789', Crypt::decryptString($usuario->contrasena_cifrada));
        $this->assertTrue(Hash::check('nueva respuesta', $usuario->respuesta_seguridad));
    }
}

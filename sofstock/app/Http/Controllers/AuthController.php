<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public const PREGUNTAS_SEGURIDAD = [
        '¿Cuál era el nombre de tu primera mascota?',
        '¿En qué ciudad naciste?',
        '¿Cuál es el segundo nombre de tu madre?',
        '¿Cuál era el nombre de tu escuela primaria?',
        '¿Cuál es tu comida favorita?',
    ];

    /**
     * Mostrar la pantalla de inicio de sesión.
     */
    public function showLogin()
    {
        return view('auth.login', [
            'setupDisponible' => ! Usuario::exists(),
        ]);
    }

    /**
     * Validar las credenciales e iniciar sesión.
     */
    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'correo' => ['required', 'email'],
            'contrasena' => ['required', 'string'],
        ]);

        $usuario = Usuario::where(
            'correo',
            $credenciales['correo']
        )->first();

        if (
            ! $usuario ||
            ! Hash::check($credenciales['contrasena'], $usuario->contrasena)
        ) {
            return back()
                ->withErrors([
                    'correo' => 'Correo o contraseña incorrectos.',
                ])
                ->onlyInput('correo');
        }

        if ($usuario->estado_aprobacion !== 'aprobado') {
            return back()
                ->withErrors([
                    'correo' => 'Correo o contraseña incorrectos, o cuenta inactiva.',
                ])
                ->onlyInput('correo');
        }

        if (! $usuario->activo) {
            return back()
                ->withErrors([
                    'correo' => 'Tu cuenta está desactivada. Contacta al administrador para que la reactive.',
                ])
                ->onlyInput('correo');
        }

        Auth::login($usuario);
        $request->session()->regenerate();

        if (! $usuario->pregunta_seguridad || ! $usuario->respuesta_seguridad) {
            return redirect()->route('security-question.setup');
        }

        return redirect()->route('dashboard.index');
    }

    public function showSecurityQuestionSetup(Request $request)
    {
        return view('auth.security-question', ['preguntas' => self::PREGUNTAS_SEGURIDAD]);
    }

    public function saveSecurityQuestion(Request $request)
    {
        $validated = $request->validate([
            'pregunta_seguridad' => ['required', Rule::in(self::PREGUNTAS_SEGURIDAD)],
            'respuesta_seguridad' => ['required', 'string', 'min:2', 'max:120'],
        ]);
        $request->user()->forceFill([
            'pregunta_seguridad' => $validated['pregunta_seguridad'],
            'respuesta_seguridad' => Hash::make(mb_strtolower(trim($validated['respuesta_seguridad']))),
        ])->save();

        return redirect()->route('dashboard.index');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function showRegistration()
    {
        if (! Usuario::exists()) {
            return redirect()->route('initial-admin.create');
        }

        if (! Usuario::administradores()->exists()) {
            return redirect()->route('login')
                ->withErrors(['correo' => 'No hay un administrador disponible para autorizar nuevas cuentas.']);
        }

        return view('auth.register', [
            'tiposDocumento' => Usuario::TIPOS_DOCUMENTO,
            'generos' => Usuario::GENEROS,
        ]);
    }

    public function register(Request $request)
    {
        if (! Usuario::exists()) {
            return redirect()->route('initial-admin.create');
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150', 'unique:usuarios,correo'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'genero' => ['nullable', Rule::in(Usuario::GENEROS)],
            'genero_detalle' => ['required_if:genero,Otro', 'nullable', 'string', 'max:100'],
            'tipo_documento' => ['nullable', Rule::in(Usuario::TIPOS_DOCUMENTO)],
            'documento' => ['nullable', 'string', 'max:30', 'unique:usuarios,documento'],
            'contrasena' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (($validated['genero'] ?? null) !== 'Otro') {
            $validated['genero_detalle'] = null;
        }

        $administradores = Usuario::administradores()->get(['id', 'correo']);
        if ($administradores->isEmpty()) {
            return redirect()->route('login')
                ->withErrors(['correo' => 'No hay un administrador disponible para autorizar nuevas cuentas.']);
        }

        $usuario = DB::transaction(function () use ($validated, $administradores): Usuario {
            $rolPendiente = Rol::query()
                ->whereRaw('LOWER(TRIM(nombre_rol)) = ?', ['pendiente'])
                ->first();

            if (! $rolPendiente) {
                $rolPendiente = Rol::create([
                    'nombre_rol' => 'pendiente',
                    'descripcion' => 'Rol temporal para cuentas pendientes de aprobación',
                ]);
            }

            $usuario = Usuario::create([
                'nombre' => $validated['nombre'],
                'apellido' => $validated['apellido'],
                'correo' => $validated['correo'],
                'telefono' => $validated['telefono'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'genero' => $validated['genero'] ?? null,
                'genero_detalle' => $validated['genero_detalle'] ?? null,
                'tipo_documento' => $validated['tipo_documento'] ?? null,
                'documento' => $validated['documento'] ?? null,
                'contrasena' => Hash::make($validated['contrasena']),
                'contrasena_cifrada' => Crypt::encryptString($validated['contrasena']),
                'id_rol' => $rolPendiente->id_rol,
                'activo' => false,
                'estado_aprobacion' => 'pendiente',
            ]);

            $notificacion = Notificacion::create([
                'titulo' => 'Nueva solicitud de registro',
                'mensaje' => "{$usuario->nombre} {$usuario->apellido} ({$usuario->correo}) solicita autorización para crear una cuenta.",
                'tipo' => Notificacion::TIPO_SOLICITUD_REGISTRO,
                'datos' => ['usuario_id' => $usuario->id],
            ]);
            $notificacion->usuariosDestinatarios()->sync($administradores);

            return $usuario;
        });

        return redirect()->route('login')
            ->with('success', 'Tu solicitud fue enviada al administrador. Podrás iniciar sesión cuando sea aprobada.');
    }

    public function findRecoveryQuestion(Request $request)
    {
        $validated = $request->validate(['correo' => ['required', 'email']]);
        $usuario = Usuario::where('correo', $validated['correo'])
            ->where('activo', true)
            ->where('estado_aprobacion', 'aprobado')
            ->first();
        if (! $usuario || ! $usuario->pregunta_seguridad || ! $usuario->respuesta_seguridad) {
            return back()->withErrors(['correo' => 'No fue posible iniciar la recuperación con esos datos.'])->onlyInput('correo');
        }
        $request->session()->put('password_recovery_user_id', $usuario->id);

        return view('auth.reset-password-security', ['pregunta' => $usuario->pregunta_seguridad]);
    }

    public function resetPasswordWithSecurityQuestion(Request $request)
    {
        $validated = $request->validate([
            'respuesta_seguridad' => ['required', 'string', 'max:120'],
            'contrasena' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $key = 'password-recovery:'.$request->ip().':'.$request->session()->get('password_recovery_user_id', 'unknown');
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['respuesta_seguridad' => 'Demasiados intentos. Intenta de nuevo en 15 minutos.']);
        }
        $usuario = Usuario::find($request->session()->get('password_recovery_user_id'));
        if (
            ! $usuario ||
            ! $usuario->activo ||
            $usuario->estado_aprobacion !== 'aprobado' ||
            ! Hash::check(mb_strtolower(trim($validated['respuesta_seguridad'])), $usuario->respuesta_seguridad ?? '')
        ) {
            RateLimiter::hit($key, 900);

            return back()->withErrors(['respuesta_seguridad' => 'Los datos no coinciden.'])->withInput($request->except('respuesta_seguridad', 'contrasena', 'contrasena_confirmation'));
        }
        $usuario->contrasena = Hash::make($validated['contrasena']);
        $usuario->contrasena_cifrada = Crypt::encryptString($validated['contrasena']);
        $usuario->fecha_actualizacion = now();
        $usuario->save();
        RateLimiter::clear($key);
        $request->session()->put('password_recovery_answer_pending', true);

        return view('auth.confirm-recovery-answer', [
            'pregunta' => $usuario->pregunta_seguridad,
            'preguntas' => self::PREGUNTAS_SEGURIDAD,
        ]);
    }

    public function saveRecoveryAnswerAgain(Request $request)
    {
        if (! $request->session()->get('password_recovery_answer_pending')) {
            return redirect()->route('password.forgot');
        }

        $validated = $request->validate([
            'pregunta_seguridad' => ['required', Rule::in(self::PREGUNTAS_SEGURIDAD)],
            'respuesta_seguridad' => ['required', 'string', 'min:2', 'max:120'],
        ]);
        $usuario = Usuario::find($request->session()->get('password_recovery_user_id'));
        if (! $usuario || ! $usuario->activo || $usuario->estado_aprobacion !== 'aprobado') {
            $request->session()->forget(['password_recovery_user_id', 'password_recovery_answer_pending']);

            return redirect()->route('login');
        }

        $usuario->pregunta_seguridad = $validated['pregunta_seguridad'];
        $usuario->respuesta_seguridad = Hash::make(mb_strtolower(trim($validated['respuesta_seguridad'])));
        $usuario->fecha_actualizacion = now();
        $usuario->save();
        $request->session()->forget(['password_recovery_user_id', 'password_recovery_answer_pending']);

        return redirect()->route('login')->with('success', 'Contraseña y respuesta de recuperación guardadas. Ya puedes iniciar sesión.');
    }

    /**
     * Mostrar el formulario para cambiar la contraseña.
     */
    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    /**
     * Actualizar la contraseña del usuario autenticado.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'contrasena_actual' => ['required', 'string'],
            'contrasena_nueva' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'different:contrasena_actual',
            ],
        ]);

        $usuario = $request->user();

        if (! Hash::check($validated['contrasena_actual'], $usuario->contrasena)) {
            return back()
                ->withErrors([
                    'contrasena_actual' => 'La contraseña actual no es correcta.',
                ]);
        }

        $usuario->contrasena = Hash::make($validated['contrasena_nueva']);
        $usuario->contrasena_cifrada = Crypt::encryptString(
            $validated['contrasena_nueva']
        );
        $usuario->fecha_actualizacion = now();
        $usuario->save();

        return redirect()
            ->route('password.change')
            ->with('success', 'Tu contraseña se cambió correctamente.');
    }

    /**
     * Cerrar sesión.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Mostrar la pantalla de inicio de sesión.
     */
    public function showLogin()
    {
        return view('auth.login');
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

        $usuario = \App\Models\Usuario::where(
            'correo',
            $credenciales['correo']
        )->first();

        if (
            !$usuario ||
            !$usuario->activo ||
            !Hash::check(
                $credenciales['contrasena'],
                $usuario->contrasena
            )
        ) {
            return back()
                ->withErrors([
                    'correo' => 'Correo o contraseña incorrectos, o cuenta inactiva.',
                ])
                ->onlyInput('correo');
        }

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('dashboard.index');
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

        if (!Hash::check($validated['contrasena_actual'], $usuario->contrasena)) {
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

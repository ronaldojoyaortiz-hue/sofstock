<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

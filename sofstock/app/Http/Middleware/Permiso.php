<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class Permiso
{
    /**
     * Verificar si el usuario tiene permiso para acceder.
     */
    public function handle(
        Request $request,
        Closure $next,
        string ...$rolesPermitidos
    ): Response {
        // Verificar si hay un usuario autenticado.
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $usuario = Auth::user();

        // Obtener el nombre del rol del usuario.
        $nombreRol = $usuario->rol?->nombre_rol;

        // Verificar si su rol está autorizado.
        if (!$nombreRol || !in_array($nombreRol, $rolesPermitidos, true)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExigirPreguntaSeguridad
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();
        if ($usuario && (!$usuario->pregunta_seguridad || !$usuario->respuesta_seguridad)
            && !$request->routeIs('security-question.setup', 'security-question.save', 'logout')) {
            return redirect()->route('security-question.setup');
        }

        return $next($request);
    }
}

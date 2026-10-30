<?php

namespace App\Http\Middleware;

use App\Services\AuditoriaService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class AuditarActividad
{
    public function __construct(private AuditoriaService $auditoriaService)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $idUsuario = Auth::id();

        try {
            $response = $next($request);
            $this->registrarSolicitud(
                $request,
                $response->getStatusCode(),
                $idUsuario
            );

            return $response;
        } catch (Throwable $exception) {
            $status = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : 500;

            $this->registrarSolicitud($request, $status, $idUsuario);

            throw $exception;
        }
    }

    private function registrarSolicitud(
        Request $request,
        int $status,
        int|string|null $idUsuario
    ): void {
        try {
            $route = $request->route();
            $nombreRuta = $route?->getName() ?? $request->path();
            $metodo = $request->method();
            $accion = match ($metodo) {
                'GET', 'HEAD' => 'CONSULTAR',
                'POST' => 'ENVIAR',
                'PUT', 'PATCH' => 'ACTUALIZAR',
                'DELETE' => 'ELIMINAR',
                default => 'SOLICITUD',
            };
            $modulo = ucfirst(explode('.', $nombreRuta)[0]);
            $parametros = [];

            foreach ($route?->parameters() ?? [] as $nombre => $valor) {
                if (is_object($valor) && method_exists($valor, 'getKey')) {
                    $parametros[$nombre] = $valor->getKey();
                } elseif (is_scalar($valor) || $valor === null) {
                    $parametros[$nombre] = $valor;
                }
            }

            $this->auditoriaService->registrar(
                accion: $accion,
                modulo: $modulo,
                descripcion: "{$metodo} {$nombreRuta} respondió HTTP {$status}.",
                datosNuevos: [
                    'ruta' => $nombreRuta,
                    'metodo' => $metodo,
                    'estado_http' => $status,
                    'parametros' => $parametros,
                    'datos_enviados' => $request->all(),
                ],
                idUsuario: $idUsuario === null ? null : (int) $idUsuario
            );
        } catch (Throwable $exception) {
            Log::error('No se pudo guardar la actividad en auditorías.', [
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}

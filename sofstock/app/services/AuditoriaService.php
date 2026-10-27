<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditoriaService
{
    /**
     * Registrar una acción realizada en el sistema.
     */
    public function registrar(
        string $accion,
        string $modulo,
        string $descripcion,
        ?string $tabla = null,
        int|string|null $idRegistro = null,
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null,
        ?string $motivo = null
    ): Auditoria {
        $request = request();

        $usuario = Auth::user();

        return Auditoria::create([
            'id_usuario' => $usuario?->getAuthIdentifier(),

            'accion' => $accion,
            'modulo' => $modulo,
            'descripcion' => $descripcion,

            'tabla_afectada' => $tabla,
            'id_registro_afectado' => $idRegistro === null
                ? null
                : (string) $idRegistro,

            'datos_anteriores' => $this->filtrarDatos(
                $datosAnteriores
            ),

            'datos_nuevos' => $this->filtrarDatos(
                $datosNuevos
            ),

            'motivo' => $motivo,

            'direccion_ip' => $request instanceof Request
                ? $request->ip()
                : null,

            'user_agent' => $request instanceof Request
                ? $request->userAgent()
                : null,
        ]);
    }

    /**
     * Evitar guardar contraseñas y otros datos sensibles.
     */
    private function filtrarDatos(?array $datos): ?array
    {
        if ($datos === null) {
            return null;
        }

        $camposSensibles = [
            'contrasena',
            'password',
            'password_confirmation',
            'remember_token',
            'token',
        ];

        foreach ($camposSensibles as $campo) {
            unset($datos[$campo]);
        }

        return $datos;
    }
}

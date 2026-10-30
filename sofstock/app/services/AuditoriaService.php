<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Http\UploadedFile;
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
        ?string $motivo = null,
        ?int $idUsuario = null
    ): Auditoria {
        $request = request();

        $usuario = Auth::user();

        return Auditoria::create([
            'id_usuario' => $idUsuario ?? $usuario?->getAuthIdentifier(),

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

        return $this->sanitizar($datos);
    }

    private function sanitizar(array $datos): array
    {
        $seguros = [];

        foreach ($datos as $campo => $valor) {
            $nombreCampo = mb_strtolower((string) $campo);
            if (
                str_contains($nombreCampo, 'password') ||
                str_contains($nombreCampo, 'contrasena') ||
                str_contains($nombreCampo, 'token') ||
                str_contains($nombreCampo, 'secret') ||
                $nombreCampo === 'clave'
            ) {
                continue;
            }

            if ($valor instanceof UploadedFile) {
                $seguros[$campo] = [
                    'archivo' => $valor->getClientOriginalName(),
                    'tipo' => $valor->getClientMimeType(),
                    'bytes' => $valor->getSize(),
                ];
            } elseif (is_array($valor)) {
                $seguros[$campo] = $this->sanitizar($valor);
            } elseif (is_scalar($valor) || $valor === null) {
                $seguros[$campo] = $valor;
            }
        }

        return $seguros;
    }
}

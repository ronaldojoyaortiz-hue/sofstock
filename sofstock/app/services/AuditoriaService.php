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
        ?int $idUsuario = null,
        bool $eliminarTodo = false
    ): Auditoria {
        $request = request();

        $usuario = Auth::user();
        $accionNormalizada = $this->normalizarAccion($accion);
        $descripcionNormalizada = $this->normalizarDescripcion(
            $accionNormalizada,
            $modulo,
            $descripcion,
            $eliminarTodo
        );

        return Auditoria::create([
            'id_usuario' => $idUsuario ?? $usuario?->getAuthIdentifier(),

            'accion' => $accionNormalizada,
            'modulo' => $modulo,
            'descripcion' => $descripcionNormalizada,

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

    private function normalizarAccion(string $accion): string
    {
        $accionNormalizada = strtoupper(trim($accion));

        if (
            str_contains($accionNormalizada, 'ELIMINAR')
            && str_contains($accionNormalizada, 'TODO')
        ) {
            return 'ELIMINAR_TODO';
        }

        return $accionNormalizada;
    }

    private function normalizarDescripcion(
        string $accion,
        string $modulo,
        string $descripcion,
        bool $eliminarTodo = false
    ): string {
        $descripcionOriginal = trim($descripcion);

        if (
            $descripcionOriginal !== ''
            && str_contains(mb_strtolower($descripcionOriginal), 'eliminar todo')
        ) {
            return $descripcionOriginal;
        }

        if ($eliminarTodo || $accion === 'ELIMINAR_TODO') {
            return 'Se ejecutó la acción "eliminar todo" en el módulo ' . $modulo . '.';
        }

        return $descripcionOriginal;
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
                str_contains($nombreCampo, 'pregunta_seguridad') ||
                str_contains($nombreCampo, 'respuesta_seguridad') ||
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

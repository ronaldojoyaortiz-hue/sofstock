<?php

namespace App\services;

use App\Models\Notificacion;
use App\Models\Usuario;
use App\Repositories\NotificacionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NotificacionService
{
    public function __construct(
        private readonly NotificacionRepository $notificaciones
    ) {}

    public function enviarManual(Usuario $emisor, array $datos): Notificacion
    {
        $idsUsuarios = array_values(array_unique(array_map(
            'intval',
            $datos['usuarios'] ?? []
        )));
        $idsRoles = array_values(array_unique(array_map(
            'intval',
            $datos['roles'] ?? []
        )));

        $usuariosDirectos = $this->notificaciones->usuariosActivosPorIds($idsUsuarios);
        if ($usuariosDirectos->count() !== count($idsUsuarios)) {
            throw ValidationException::withMessages([
                'usuarios' => 'Uno o más destinatarios no existen o están inactivos.',
            ]);
        }

        $destinatarios = $usuariosDirectos
            ->merge($this->notificaciones->usuariosActivosPorRoles($idsRoles))
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        if ($destinatarios === []) {
            throw ValidationException::withMessages([
                'destinatarios' => 'Selecciona al menos un usuario activo o un rol con usuarios activos.',
            ]);
        }

        return DB::transaction(function () use ($emisor, $datos, $destinatarios): Notificacion {
            return $this->notificaciones->crear([
                'usuario_emisor_id' => $emisor->getAuthIdentifier(),
                'titulo' => $datos['titulo'],
                'mensaje' => $datos['mensaje'],
                'tipo' => Notificacion::TIPO_MANUAL,
                'fecha_evento' => $datos['fecha_evento'] ?? null,
                'lugar' => $datos['lugar'] ?? null,
            ], $destinatarios);
        });
    }
}

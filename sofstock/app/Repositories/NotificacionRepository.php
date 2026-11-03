<?php

namespace App\Repositories;

use App\Models\Notificacion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class NotificacionRepository
{
    public function listarRecibidas(Usuario $usuario): LengthAwarePaginator
    {
        return $usuario->notificacionesRecibidas()
            ->with([
                'emisor:id,nombre,apellido',
                'producto:id,nombre',
            ])
            ->orderByDesc('notificacion_usuario.created_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function contarNoLeidas(Usuario $usuario): int
    {
        return $usuario->notificacionesRecibidas()
            ->wherePivotNull('leida_at')
            ->count();
    }

    public function rolesDisponibles(): Collection
    {
        return Rol::query()
            ->whereHas('usuarios', fn ($query) => $query->where('activo', true))
            ->orderBy('nombre_rol')
            ->get();
    }

    public function usuariosActivosDisponibles(): Collection
    {
        return Usuario::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get(['id', 'nombre', 'apellido', 'correo', 'id_rol']);
    }

    public function usuariosActivosPorIds(array $ids): Collection
    {
        return Usuario::query()
            ->where('activo', true)
            ->whereIn('id', $ids)
            ->get(['id']);
    }

    public function usuariosActivosPorRoles(array $idsRoles): Collection
    {
        return Usuario::query()
            ->where('activo', true)
            ->whereIn('id_rol', $idsRoles)
            ->get(['id']);
    }

    public function crear(array $datos, array $idsDestinatarios): Notificacion
    {
        $notificacion = Notificacion::query()->create($datos);
        $notificacion->usuariosDestinatarios()->sync($idsDestinatarios);

        return $notificacion->load(['emisor', 'producto', 'usuariosDestinatarios']);
    }

    public function eliminar(Notificacion $notificacion): void
    {
        $notificacion->delete();
    }

    public function marcarComoLeida(Usuario $usuario, Notificacion $notificacion): bool
    {
        $relacion = $usuario->notificacionesRecibidas()
            ->whereKey($notificacion->getKey());

        if (! $relacion->exists()) {
            return false;
        }

        $usuario->notificacionesRecibidas()->updateExistingPivot(
            $notificacion->getKey(),
            ['leida_at' => now()]
        );

        return true;
    }
}

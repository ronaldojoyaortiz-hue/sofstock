<?php

namespace App\Repositories;

use App\Models\Auditoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditoriaRepository
{
    /**
     * Registrar una nueva acción en la auditoría.
     */
    public function crear(array $datos): Auditoria
    {
        return Auditoria::create($datos);
    }

    /**
     * Buscar un registro de auditoría por su ID.
     */
    public function buscarPorId(int $id): ?Auditoria
    {
        return Auditoria::with('usuario')
            ->find($id);
    }

    /**
     * Consultar la auditoría con filtros opcionales.
     */
    public function listar(array $filtros = []): LengthAwarePaginator
    {
        $consulta = Auditoria::with('usuario')
            ->orderByDesc('created_at');

        if (!empty($filtros['usuario'])) {
            $consulta->where(
                'id_usuario',
                $filtros['usuario']
            );
        }

        if (!empty($filtros['accion'])) {
            $consulta->where(
                'accion',
                $filtros['accion']
            );
        }

        if (!empty($filtros['modulo'])) {
            $consulta->where(
                'modulo',
                $filtros['modulo']
            );
        }

        if (!empty($filtros['fecha_desde'])) {
            $consulta->whereDate(
                'created_at',
                '>=',
                $filtros['fecha_desde']
            );
        }

        if (!empty($filtros['fecha_hasta'])) {
            $consulta->whereDate(
                'created_at',
                '<=',
                $filtros['fecha_hasta']
            );
        }

        return $consulta->paginate(15)->withQueryString();
    }
}

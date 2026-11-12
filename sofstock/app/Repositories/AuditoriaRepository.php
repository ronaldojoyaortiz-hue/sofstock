<?php

namespace App\Repositories;

use App\Models\Auditoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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
    public function buscarPorId(int $id, int $usuarioId): ?Auditoria
    {
        return Auditoria::with('usuario')
            ->whereKey($id)
            ->whereNotExists(function ($query) use ($usuarioId): void {
                $query->selectRaw('1')
                    ->from('auditoria_usuario_oculta')
                    ->whereColumn('auditoria_usuario_oculta.id_auditoria', 'auditorias.id_auditoria')
                    ->where('auditoria_usuario_oculta.id_usuario', $usuarioId);
            })
            ->first();
    }

    /**
     * Consultar la auditoría con filtros opcionales.
     */
    public function listar(array $filtros, int $usuarioId): LengthAwarePaginator
    {
        $consulta = Auditoria::with('usuario')
            ->whereNotExists(function ($query) use ($usuarioId): void {
                $query->selectRaw('1')
                    ->from('auditoria_usuario_oculta')
                    ->whereColumn('auditoria_usuario_oculta.id_auditoria', 'auditorias.id_auditoria')
                    ->where('auditoria_usuario_oculta.id_usuario', $usuarioId);
            })
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

    public function ocultarParaUsuario(int $auditoriaId, int $usuarioId): void
    {
        DB::table('auditoria_usuario_oculta')->insertOrIgnore([
            'id_auditoria' => $auditoriaId,
            'id_usuario' => $usuarioId,
            'created_at' => now(),
        ]);
    }

    public function ocultarVariasParaUsuario(array $auditoriaIds, int $usuarioId): int
    {
        $idsVisibles = Auditoria::query()
            ->whereIn('id_auditoria', $auditoriaIds)
            ->whereNotExists(function ($query) use ($usuarioId): void {
                $query->selectRaw('1')
                    ->from('auditoria_usuario_oculta')
                    ->whereColumn('auditoria_usuario_oculta.id_auditoria', 'auditorias.id_auditoria')
                    ->where('auditoria_usuario_oculta.id_usuario', $usuarioId);
            })
            ->pluck('id_auditoria');

        if ($idsVisibles->isEmpty()) {
            return 0;
        }

        $ahora = now();
        $ocultas = $idsVisibles->map(fn ($id): array => [
            'id_auditoria' => $id,
            'id_usuario' => $usuarioId,
            'created_at' => $ahora,
        ])->all();

        return DB::table('auditoria_usuario_oculta')->insertOrIgnore($ocultas);
    }

    public function ocultarTodasParaUsuario(int $usuarioId): int
    {
        $cantidad = 0;

        Auditoria::query()
            ->select('id_auditoria')
            ->whereNotExists(function ($query) use ($usuarioId): void {
                $query->selectRaw('1')
                    ->from('auditoria_usuario_oculta')
                    ->whereColumn('auditoria_usuario_oculta.id_auditoria', 'auditorias.id_auditoria')
                    ->where('auditoria_usuario_oculta.id_usuario', $usuarioId);
            })
            ->chunkById(500, function ($auditorias) use ($usuarioId, &$cantidad): void {
                $ahora = now();
                $ocultas = $auditorias->map(fn (Auditoria $auditoria): array => [
                    'id_auditoria' => $auditoria->id_auditoria,
                    'id_usuario' => $usuarioId,
                    'created_at' => $ahora,
                ])->all();

                $cantidad += DB::table('auditoria_usuario_oculta')->insertOrIgnore($ocultas);
            }, 'id_auditoria');

        return $cantidad;
    }
}

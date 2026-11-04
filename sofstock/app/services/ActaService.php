<?php

namespace App\Services;

use App\Models\Acta;
use App\Models\Rol;
use App\Repositories\ActaRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActaService
{
    public function __construct(
        protected ActaRepository $actaRepository
    ) {}

    public function listar(): LengthAwarePaginator
    {
        return $this->actaRepository->paginate();
    }

    public function consultar(int $id): Acta
    {
        return $this->actaRepository->findOrFail($id);
    }

    public function registrar(array $data): Acta
    {
        return DB::transaction(function () use ($data) {
            $usuario = Auth::user();
            $rol = $usuario->rol;
            $productos = $this->productosParaSincronizar($data);
            $firmantes = $this->firmantesParaSincronizar($data);
            unset($data['productos']);
            unset($data['rol_firmantes_id']);

            $data['usuario_id'] = $usuario->id;
            $data['usuario_nombre'] = trim(
                $usuario->nombre . ' ' . $usuario->apellido
            );

            $data['rol_id'] = $usuario->id_rol;
            $data['rol_nombre'] = $rol?->nombre_rol;

            $acta = $this->actaRepository->create($data);
            $acta->productos()->sync($productos);
            $acta->firmas()->createMany($firmantes);

            return $acta;
        });
    }

    public function actualizar(Acta $acta, array $data): bool
    {
        return DB::transaction(function () use ($acta, $data): bool {
            $productos = $this->productosParaSincronizar($data);
            $firmantes = $this->firmantesParaSincronizar($data);
            unset($data['productos']);
            unset($data['rol_firmantes_id']);

            $actualizada = $this->actaRepository->update($acta, $data);
            $acta->productos()->sync($productos);
            $acta->firmas()->delete();
            $acta->firmas()->createMany($firmantes);

            return $actualizada;
        });
    }

    public function eliminar(Acta $acta): bool
    {
        return $this->actaRepository->delete($acta);
    }

    private function productosParaSincronizar(array $data): array
    {
        if (($data['tipo'] ?? null) !== 'entrega_bienes') {
            return [];
        }

        return collect($data['productos'] ?? [])
            ->mapWithKeys(fn (array $producto): array => [
                $producto['producto_id'] => [
                    'cantidad' => $producto['cantidad'],
                    'observaciones' => $producto['observaciones'] ?? null,
                ],
            ])
            ->all();
    }

    private function firmantesParaSincronizar(array $data): array
    {
        $rol = Rol::query()
            ->with(['usuarios' => fn ($query) => $query
                ->where('activo', true)
                ->orderBy('apellido')
                ->orderBy('nombre')])
            ->find($data['rol_firmantes_id'] ?? null);

        if (
            ! $rol ||
            in_array(mb_strtolower($rol->nombre_rol), ['presidente', 'secretaria'], true) ||
            $rol->usuarios->isEmpty()
        ) {
            throw ValidationException::withMessages([
                'rol_firmantes_id' => 'Selecciona un rol permitido que tenga usuarios activos para firmar el acta.',
            ]);
        }

        return $rol->usuarios->map(fn ($usuario): array => [
            'rol_id' => $rol->id_rol,
            'rol_nombre' => $rol->nombre_rol,
            'usuario_id' => $usuario->id,
            'nombre' => trim($usuario->nombre . ' ' . $usuario->apellido),
            'cargo' => $rol->nombre_rol,
        ])->all();
    }
}

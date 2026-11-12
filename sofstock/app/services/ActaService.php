<?php

namespace App\Services;

use App\Models\Acta;
use App\Models\Producto;
use App\Models\Usuario;
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
            $firmantes = $this->prepararFirmantes($data);

            $data['tipo'] = $this->tipoLegado($data['tipo_acta']);
            $data['productos'] = $this->productos($data);
            $data['usuario_id'] = $usuario->id;
            $data['usuario_nombre'] = trim(
                $usuario->nombre.' '.$usuario->apellido
            );

            $data['rol_id'] = $usuario->id_rol;
            $data['rol_nombre'] = $rol?->nombre_rol;
            $data = array_merge($data, $firmantes);

            return $this->actaRepository->create($data);
        });
    }

    public function actualizar(Acta $acta, array $data): bool
    {
        return DB::transaction(function () use ($acta, $data): bool {
            $firmantes = $this->prepararFirmantes($data, $acta);
            $data['tipo'] = $this->tipoLegado($data['tipo_acta']);
            $data['productos'] = $this->productos($data);
            unset($data['firmantes']);
            $data = array_merge($data, $firmantes);

            return $this->actaRepository->update($acta, $data);
        });
    }

    public function cambiarEstado(Acta $acta, string $estado): bool
    {
        if (! in_array($acta->estado, ['borrador', 'pendiente_aprobacion'], true)) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se puede cambiar el estado de actas en borrador o emitidas.',
            ]);
        }

        if (! in_array($estado, ['borrador', 'pendiente_aprobacion', 'aprobada', 'firmada', 'anulada'], true)) {
            throw ValidationException::withMessages([
                'estado' => 'Selecciona un estado válido para el acta.',
            ]);
        }

        return $this->actaRepository->update($acta, ['estado' => $estado]);
    }

    public function eliminar(Acta $acta): bool
    {
        return $this->actaRepository->delete($acta);
    }

    private function productos(array $data): array
    {
        $lineas = collect($data['productos'] ?? []);
        $productos = Producto::query()
            ->whereIn('id', $lineas->pluck('producto_id')->filter())
            ->get(['id', 'nombre'])
            ->keyBy('id');
        if ($productos->count() !== $lineas->count()) {
            throw ValidationException::withMessages([
                'productos' => 'Uno o más productos seleccionados ya no están disponibles.',
            ]);
        }

        return $lineas
            ->map(fn (array $linea): array => [
                'producto_id' => (int) $linea['producto_id'],
                'nombre' => $productos->get($linea['producto_id'])?->nombre,
                'cantidad' => (float) $linea['cantidad'],
                'observaciones' => $linea['observaciones'] ?? null,
            ])
            ->all();
    }

    private function prepararFirmantes(array $data, ?Acta $acta = null): array
    {
        if (! array_key_exists('firmantes', $data) && $acta) {
            $firmantes = $acta->firmantesParaMostrar();
            $datos = ['firmantes' => $firmantes];
            foreach ([1, 2] as $numero) {
                $firmante = $firmantes[$numero - 1] ?? null;
                $prefijo = "firmante_{$numero}_";
                $datos[$prefijo.'usuario_id'] = $firmante['usuario_id'] ?? null;
                $datos[$prefijo.'nombre'] = $firmante['nombre'] ?? null;
                $datos[$prefijo.'cargo'] = $firmante['cargo'] ?? null;
                $datos[$prefijo.'rol_id'] = $firmante['rol_id'] ?? null;
                $datos[$prefijo.'rol'] = $firmante['rol'] ?? null;
            }

            return $datos;
        }

        $rolesSeleccionados = collect($data['roles_firmantes'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $usuarios = Usuario::query()
            ->with('rol')
            ->where('activo', true)
            ->whereIn('id_rol', $rolesSeleccionados)
            ->orderBy('id_rol')
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();
        $rolesConUsuarios = $usuarios->pluck('id_rol')->unique()->map(fn ($id): int => (int) $id);
        if ($rolesSeleccionados->diff($rolesConUsuarios)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'roles_firmantes' => 'Cada rol seleccionado debe tener al menos un usuario activo.',
            ]);
        }

        $firmantes = [];
        foreach ($usuarios as $firmante) {
            $rolNombre = $firmante->rol?->nombre_rol;
            if (! $rolNombre) {
                throw ValidationException::withMessages([
                    'roles_firmantes' => 'No se pudo determinar el rol de uno de los usuarios seleccionados.',
                ]);
            }

            $firmantes[] = [
                'usuario_id' => (int) $firmante->id,
                'rol_id' => (int) $firmante->id_rol,
                'rol' => $rolNombre,
                'nombre' => trim($firmante->nombre.' '.$firmante->apellido),
                'cargo' => $rolNombre,
            ];
        }

        $datos = ['firmantes' => $firmantes];
        foreach ([1, 2] as $numero) {
            $firmante = $firmantes[$numero - 1] ?? null;
            $prefijo = "firmante_{$numero}_";
            $datos[$prefijo.'usuario_id'] = $firmante['usuario_id'] ?? null;
            $datos[$prefijo.'nombre'] = $firmante['nombre'] ?? null;
            $datos[$prefijo.'cargo'] = $firmante['cargo'] ?? null;
            $datos[$prefijo.'rol_id'] = $firmante['rol_id'] ?? null;
            $datos[$prefijo.'rol'] = $firmante['rol'] ?? null;
        }

        return $datos;
    }

    private function tipoLegado(string $tipoActa): string
    {
        return match ($tipoActa) {
            'entrega', 'recibo', 'inventario' => 'entrega_bienes',
            'devolucion' => 'entrega_inmueble',
            'junta_directiva' => 'junta_directiva',
            'asamblea' => 'asamblea',
            default => throw ValidationException::withMessages([
                'tipo_acta' => 'Selecciona un tipo de acta válido.',
            ]),
        };
    }
}

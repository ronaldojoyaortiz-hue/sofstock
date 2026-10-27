<?php

namespace App\Repositories;

use App\Models\Inmobiliario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InmobiliarioRepository
{
    public function listar(): LengthAwarePaginator
    {
        return Inmobiliario::orderBy('id_inmobiliario', 'desc')
            ->paginate(10);
    }

    public function buscarPorId(int $id): Inmobiliario
    {
        return Inmobiliario::findOrFail($id);
    }

    public function crear(array $datos): Inmobiliario
    {
        return Inmobiliario::create($datos);
    }

    public function actualizar(
        int $id,
        array $datos
    ): Inmobiliario {
        $inmobiliario = $this->buscarPorId($id);

        $inmobiliario->update($datos);

        return $inmobiliario->refresh();
    }

    public function eliminar(int $id): bool
    {
        $inmobiliario = $this->buscarPorId($id);

        return (bool) $inmobiliario->delete();
    }
}

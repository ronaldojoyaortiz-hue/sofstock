<?php

namespace App\Services;

use App\Models\Inmobiliario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class InmobiliarioService
{
    public function listar(): LengthAwarePaginator
    {
        return Inmobiliario::query()
            ->orderByDesc('id_inmueble')
            ->paginate(10);
    }

    public function obtener(int $id): Inmobiliario
    {
        return Inmobiliario::findOrFail($id);
    }

    public function crear(array $datos): Inmobiliario
    {
        $datos = $this->compatibilizarNombreLegacy($datos);

        return Inmobiliario::create($datos);
    }

    public function actualizar(int $id, array $datos): Inmobiliario
    {
        $inmobiliario = $this->obtener($id);
        $datos = $this->compatibilizarNombreLegacy($datos);
        $inmobiliario->update($datos);

        return $inmobiliario->refresh();
    }

    public function eliminar(int $id): bool
    {
        return (bool) $this->obtener($id)->delete();
    }

    private function compatibilizarNombreLegacy(array $datos): array
    {
        if (Schema::hasColumn('inmuebles', 'nombre') && isset($datos['tipo_inmueble'])) {
            $datos['nombre'] = $datos['tipo_inmueble'];
        }

        return $datos;
    }
}

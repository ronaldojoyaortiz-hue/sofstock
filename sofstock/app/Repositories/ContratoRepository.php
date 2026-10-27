<?php

namespace App\Repositories;

use App\Models\Contrato;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContratoRepository
{
    public function listarPorInmueble(int $idInmueble): LengthAwarePaginator
    {
        return Contrato::where('id_inmueble', $idInmueble)
            ->orderByDesc('fecha_inicio')
            ->paginate(10);
    }

    public function buscarPorInmueble(int $idInmueble, int $idContrato): Contrato
    {
        return Contrato::where('id_inmueble', $idInmueble)
            ->where('id_contrato', $idContrato)
            ->firstOrFail();
    }

    public function crear(int $idInmueble, array $datos): Contrato
    {
        $datos['id_inmueble'] = $idInmueble;

        return Contrato::create($datos);
    }

    public function actualizar(int $idInmueble, int $idContrato, array $datos): Contrato
    {
        $contrato = $this->buscarPorInmueble($idInmueble, $idContrato);
        $contrato->update($datos);

        return $contrato->refresh();
    }

    public function eliminar(int $idInmueble, int $idContrato): bool
    {
        return (bool) $this->buscarPorInmueble($idInmueble, $idContrato)->delete();
    }
}

<?php

namespace App\Services;

use App\Models\Contrato;
use App\Repositories\ContratoRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContratoService
{
    public function __construct(private ContratoRepository $contratoRepository)
    {
    }

    public function listarPorInmueble(int $idInmueble): LengthAwarePaginator
    {
        return $this->contratoRepository->listarPorInmueble($idInmueble);
    }

    public function obtener(int $idInmueble, int $idContrato): Contrato
    {
        return $this->contratoRepository->buscarPorInmueble($idInmueble, $idContrato);
    }

    public function crear(int $idInmueble, array $datos): Contrato
    {
        return $this->contratoRepository->crear($idInmueble, $datos);
    }

    public function actualizar(int $idInmueble, int $idContrato, array $datos): Contrato
    {
        return $this->contratoRepository->actualizar($idInmueble, $idContrato, $datos);
    }

    public function eliminar(int $idInmueble, int $idContrato): bool
    {
        return $this->contratoRepository->eliminar($idInmueble, $idContrato);
    }
}

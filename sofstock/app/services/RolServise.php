<?php

namespace App\Services;
use App\Repositories\RolRepository;

class RolService
{
    private RolRepository $rolRepository;

    public function __construct(RolRepository $rolRepository)
    {
        $this->rolRepository = $rolRepository;
    }

    public function listartodo()
    {
       return $this->rolRepository->listartodo();
    }

    public function guardar(array $datos)
    {
        return $this->rolRepository->guardar($datos);
    }

    public function buscar($id)
    {
        return $this->rolRepository->buscar($id);
    }

    public function actualizar($id, array $datos)
    {
        return $this->rolRepository->actualizar($id, $datos);
    }

    public function eliminar($id)
    {
        return $this->rolRepository->eliminar($id);
    }

}    
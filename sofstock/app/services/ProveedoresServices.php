<?php

namespace App\Services;

use App\Repositories\ProveedoresRepository;

class ProveedoresServices
{
    private ProveedoresRepository $proveedoresRepository;

    public function __construct(ProveedoresRepository $proveedoresRepository)
    {
        $this->proveedoresRepository = $proveedoresRepository;
    }

    public function listartodo()
    {
        return $this->proveedoresRepository->listartodo();
    }

    public function guardar(array $datos)
    {
        return $this->proveedoresRepository->guardar($datos);
    }

    public function buscar($id)
    {
        return $this->proveedoresRepository->buscar($id);
    }

    public function actualizar($id, array $datos)
    {
        return $this->proveedoresRepository->actualizar($id, $datos);
    }

    public function eliminar($id)
    {
        return $this->proveedoresRepository->eliminar($id);
    }
}

<?php

namespace App\Services;

use App\Repositories\CategoriaRepository;

class CategoriaService
{
    private CategoriaRepository $categoriaRepository;

    public function __construct(CategoriaRepository $categoriaRepository)
    {
        $this->categoriaRepository = $categoriaRepository;
    }

    public function listartodo()
    {
        return $this->categoriaRepository->listartodo();
    }

    public function guardar(array $datos)
    {
        return $this->categoriaRepository->guardar($datos);
    }

    public function buscar($id)
    {
        return $this->categoriaRepository->buscar($id);
    }

    public function actualizar($id, array $datos)
    {
        return $this->categoriaRepository->actualizar($id, $datos);
    }

    public function eliminar($id)
    {
        return $this->categoriaRepository->eliminar($id);
    }
}
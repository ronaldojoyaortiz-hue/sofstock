<?php

namespace App\Services;

use App\Repositories\ProductoRepository;

class ProductoService
{
    private ProductoRepository $productoRepository;

    public function __construct(ProductoRepository $productoRepository)
    {
        $this->productoRepository = $productoRepository;
    }

    public function listartodo()
    {
        return $this->productoRepository->listartodo();
    }

    public function guardar(array $datos)
    {
        return $this->productoRepository->guardar($datos);
    }

    public function buscar($id)
    {
        return $this->productoRepository->buscar($id);
    }

    public function actualizar($id, array $datos)
    {
        return $this->productoRepository->actualizar($id, $datos);
    }

    public function eliminar($id)
    {
        return $this->productoRepository->eliminar($id);
    }
}

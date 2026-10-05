<?php

namespace App\Repositories;
use App\Models\DetalleFactura;

class DetalleFacturaRepository
{
    public function listartodo()
    {
        return DetalleFactura::all();
    }

    public function guardar(array $datos)
    {
        return DetalleFactura::create($datos);
    }

    public function buscar($id)
    {
        return DetalleFactura::findOrFail($id);
    }


    public function actualizar($id, array $datos)
    {
      $detalleFactura = DetalleFactura::findOrFail($id);
        $detalleFactura->update($datos);
            return $detalleFactura;
    }

    public function eliminar($id)
    {
        return DetalleFactura::destroy($id);
    }
}
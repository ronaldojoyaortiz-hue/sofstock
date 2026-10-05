<?php

namespace App\Repositories;

use App\Models\Producto;

class ProductoRepository
{
    public function listartodo()
    {
        return Producto::all();
    }

    public function guardar(array $datos)
    {
        return Producto::create($datos);
    }

    public function buscar($id)
    {
        return Producto::findOrFail($id);
    }
   
    public function actualizar($id, array $data)
    {
        $producto = Producto::find($id);
        $producto->update($data);
        return $producto;
    }

    public function eliminar($id)
    {
        return Producto::destroy($id);
    }
}

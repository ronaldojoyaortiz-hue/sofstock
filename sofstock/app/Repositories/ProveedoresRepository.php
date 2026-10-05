<?php

namespace App\Repositories;

use App\Models\Proveedores;

class ProveedoresRepository
{
    public function listartodo()
    {
        return Proveedores::all();
    }

    public function guardar(array $datos)
    {
        return Proveedores::create($datos);
    }

    public function buscar($id)
    {
        return Proveedores::findOrFail($id);
    }

    public function actualizar($id, array $data)
    {
        $proveedor = Proveedores::find($id);
        $proveedor->update($data);
        return $proveedor;
    }

    public function eliminar($id)
    {
        return Proveedores::destroy($id);
    }
}

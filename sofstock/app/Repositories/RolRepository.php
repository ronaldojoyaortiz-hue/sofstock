<?php

namespace App\Services;
use App\Repositories\RolRepository;

class RolService
{
    protected $rolRepository;

    public function listartodo()
    {
       return Rol::all();
    }

    public function guardar(array $datos)
    {
        return Rol::create($datos);
    }

    public function buscar($id)
    {
        return Rol::findOrFail($id);
    }

    public function actualizar($id, array $datos)
    {
        $rol = Rol::findOrFail($id);
        $rol->update($datos);
        return $rol;
    }

    public function eliminar($id)
    {
        return Rol::destroy($id);
    }
    
}
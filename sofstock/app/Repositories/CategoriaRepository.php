<?php

namespace App\Repositories;
use App\Models\Categoria;

class CategoriaRepository
{
    public function listartodo()
    {
        return Categoria::all();
    }

    public function guardar(array $datos)
    {
        return Categoria::create($datos);
    }

    public function buscar($id)
    {
        return Categoria::findOrFail($id);
    }
  

    public function actualizar($id, array $datos)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->update($datos);
        return $categoria;
    }

   
    public function eliminar($id)
    {
      return $categoria::destroy($id);
    }
}
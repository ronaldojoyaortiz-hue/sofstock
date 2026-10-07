<?php

namespace app\Repositories;

use App\Models\Usuario;

class UsuarioRepository
{
    public function getAllUsuarios()
    {
        return Usuario::all();
    }

    public function getUsuarioById($id)
    {
        return Usuario::findOrFail($id);
    }

    public function createUsuario(array $data)
    {
        return Usuario::create($data);
    }

    public function updateUsuario($id, array $data)
    {
        $usuario = $this->getUsuarioById($id);
        $usuario->update($data);
        return $usuario;
    }

    public function deleteUsuario($id)
    {
        $usuario = $this->getUsuarioById($id);
        $usuario->delete();
        return true;
    }
}

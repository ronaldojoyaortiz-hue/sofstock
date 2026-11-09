<?php

namespace App\Http\Controllers;

use App\Http\Requests\RolStoreRequest;
use App\Models\Rol;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::query()
            ->whereRaw('LOWER(TRIM(nombre_rol)) != ?', ['secretaria'])
            ->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(RolStoreRequest $request)
    {
        Rol::create($request->validated());

        return redirect()->route('roles.index')
            ->with('success', 'Rol creado exitosamente.');
    }

    public function show(string $id)
    {
        return redirect()->route('roles.edit', $id);
    }

    public function edit(string $id)
    {
        $rol = Rol::findOrFail($id);

        return view('roles.edit', compact('rol'));
    }

    public function update(RolStoreRequest $request, string $id)
    {
        $rol = Rol::findOrFail($id);

        if (mb_strtolower(trim($rol->nombre_rol)) === 'administrador') {
            return redirect()->route('roles.index')
                ->with('error', 'El rol administrador está protegido y no se puede modificar.');
        }

        $rol->update($request->validated());

        return redirect()->route('roles.index')
            ->with('success', 'Rol actualizado exitosamente.');
    }

    public function destroy(string $id)
    {
        $rol = Rol::findOrFail($id);

        if (mb_strtolower(trim($rol->nombre_rol)) === 'administrador') {
            return redirect()->route('roles.index')
                ->with('error', 'El rol administrador está protegido y no se puede eliminar.');
        }

        $rol->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado exitosamente.');
    }
}

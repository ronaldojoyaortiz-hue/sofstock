<?php

namespace App\Http\Controllers;

use App\Http\Requests\RolStoreRequest;
use App\Models\Rol;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::all();

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
        $rol->update($request->validated());

        return redirect()->route('roles.index')
            ->with('success', 'Rol actualizado exitosamente.');
    }

    public function destroy(string $id)
    {
        Rol::destroy($id);

        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado exitosamente.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\ProveedoresServices;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function __construct(private ProveedoresServices $proveedoresService) {}

    public function index()
    {
        $proveedores = $this->proveedoresService->listartodo();

        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'razon_social' => 'required|string|max:150',
            'nit' => 'nullable|string|max:20|unique:proveedores,nit',
            'contacto_nombre' => 'nullable|string|max:100',
            'correo' => 'nullable|email|max:150',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'activo' => 'nullable|boolean',
        ]);

        $this->proveedoresService->guardar($validated);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor creado correctamente.');
    }

    public function show($id)
    {
        $proveedor = $this->proveedoresService->buscar($id);

        return view('proveedores.show', compact('proveedor'));
    }

    public function edit($id)
    {
        $proveedor = $this->proveedoresService->buscar($id);

        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'razon_social' => 'required|string|max:150',
            'nit' => 'nullable|string|max:20|unique:proveedores,nit,' . $id . ',id_proveedor',
            'contacto_nombre' => 'nullable|string|max:100',
            'correo' => 'nullable|email|max:150',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'activo' => 'nullable|boolean',
        ]);

        $this->proveedoresService->actualizar($id, $validated);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy($id)
    {
        $this->proveedoresService->eliminar($id);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor eliminado correctamente.');
    }
}

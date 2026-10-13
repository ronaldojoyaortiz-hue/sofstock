<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    /**
     * Mostrar el listado de recursos.
     */
    public function index()
    {
        $categorias = Categoria::all();
        return view('categorias.index', compact('categorias'));
    }

    /**
     * Mostrar el formulario para crear un recurso.
     */
    public function create()
    {
        return view('categorias.create');
    }

    /**
     * Guardar un recurso recién creado.
     */
    public function store(Request $request)
    {
        Categoria::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'activa' => $request->activa,
        ]);
        return redirect()
         ->route('categorias.index')
         ->with('success', 'Categoria creada exitosamente.');
    }

    /**
     * Mostrar el recurso indicado.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Mostrar el formulario para editar el recurso indicado.
     */
    public function edit(string $id)
    {
        $categoria = Categoria::findOrFail($id);
        return view('categorias.edit', compact('categoria'));
    }

    /**
     * Actualizar el recurso indicado.
     */
    public function update(Request $request, string $id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'activa' => $request->activa,
        ]);
        return redirect()->route('categorias.index')
        ->with('success', 'Categoria actualizada exitosamente.');
    }

    /**
     * Eliminar el recurso indicado.
     */
    public function destroy(string $id)
    {
        Categoria::destroy($id);
        return redirect()
        ->route('categorias.index')
        ->with('success', 'Categoria eliminada exitosamente.');
    }
}

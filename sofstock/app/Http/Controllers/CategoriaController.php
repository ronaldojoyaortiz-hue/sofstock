<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categorias = Categoria::all();
        return view('categorias.index', compact('categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categorias.create');
    }

    /**
     * Store a newly created resource in storage.
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $categoria = Categoria::findOrFail($id);
        return view('categorias.edit', compact('categoria'));
    }

    /**
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Categoria::destroy($id);
        return redirect()
        ->route('categorias.index')
        ->with('success', 'Categoria eliminada exitosamente.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = Usuario::with('rol')->paginate(15);
        return view('usuario.index', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Rol::orderBy('nombre_rol')->get();

        return view('usuario.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150', 'unique:usuarios,correo'],
            'contrasena' => ['required', 'string', 'min:8', 'confirmed'],
            'id_rol' => ['required', 'integer', 'exists:roles,id_rol'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $validated['contrasena'] = Hash::make($validated['contrasena']);
        Usuario::create($validated);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {
        return redirect()->route('usuarios.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $usuario = Usuario::findOrFail($id);
        $roles = Rol::orderBy('nombre_rol')->get();

        return view('usuario.edit', compact('usuario', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $usuario = Usuario::findOrFail($id);
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'correo')->ignore($usuario->id)],
            'contrasena' => ['nullable', 'string', 'min:8', 'confirmed'],
            'id_rol' => ['required', 'integer', 'exists:roles,id_rol'],
            'activo' => ['nullable', 'boolean'],
        ]);

        if (filled($validated['contrasena'] ?? null)) {
            $validated['contrasena'] = Hash::make($validated['contrasena']);
        } else {
            unset($validated['contrasena']);
        }

        $validated['activo'] = $request->boolean('activo');
        $usuario->update($validated);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Usuario::destroy($id);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}

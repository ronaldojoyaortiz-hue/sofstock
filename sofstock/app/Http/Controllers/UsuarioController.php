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
     * Mostrar el listado de recursos.
     */
    public function index()
    {
        $usuarios = Usuario::with('rol')->paginate(15);
        return view('usuario.index', compact('usuarios'));
    }

    /**
     * Mostrar el formulario para crear un recurso.
     */
    public function create()
    {
        $roles = Rol::orderBy('nombre_rol')->get();

        return view('usuario.create', compact('roles'));
    }

    /**
     * Guardar un recurso recién creado.
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
        $validated['activo'] = $request->boolean('activo');
        Usuario::create($validated);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Mostrar el recurso indicado.
     */
    public function show(String $id)
    {
        return redirect()->route('usuarios.edit', $id);
    }

    /**
     * Mostrar el formulario para editar el recurso indicado.
     */
    public function edit(string $id)
    {
        $usuario = Usuario::findOrFail($id);
        $roles = Rol::orderBy('nombre_rol')->get();

        return view('usuario.edit', compact('usuario', 'roles'));
    }

    /**
     * Actualizar el recurso indicado.
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
        $validated['fecha_actualizacion'] = now();
        $usuario->update($validated);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Eliminar el recurso indicado.
     */
    public function destroy(string $id)
    {
        Usuario::findOrFail($id)->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}

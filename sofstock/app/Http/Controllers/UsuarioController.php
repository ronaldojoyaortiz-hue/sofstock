<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    private const TIPOS_DOCUMENTO = [
        'Tarjeta de identidad',
        'Cédula de ciudadanía',
        'Cédula de extranjería',
        'Pasaporte',
    ];

    private const GENEROS = [
        'Masculino',
        'Femenino',
        'No binario',
        'Otro',
        'Prefiero no responder',
    ];

    /**
     * Mostrar el listado de usuarios.
     */
    public function index()
    {
        $usuarios = Usuario::with('rol')->paginate(15);

        return view('usuario.index', compact('usuarios'));
    }

    /**
     * Mostrar el formulario para crear un usuario.
     */
    public function create()
    {
        $roles = Rol::orderBy('nombre_rol')->get();

        return view('usuario.create', [
            'roles' => $roles,
            'tiposDocumento' => self::TIPOS_DOCUMENTO,
            'generos' => self::GENEROS,
        ]);
    }

    /**
     * Guardar un usuario recién creado.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'correo' => [
                'required',
                'email',
                'max:150',
                'unique:usuarios,correo',
            ],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'genero' => ['nullable', 'string', Rule::in(self::GENEROS)],
            'genero_detalle' => ['nullable', 'string', 'max:100'],
            'tipo_documento' => [
                'nullable',
                'string',
                Rule::in(self::TIPOS_DOCUMENTO),
            ],
            'documento' => ['nullable', 'string', 'max:50'],
            'contrasena' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'id_rol' => [
                'required',
                'integer',
                'exists:roles,id_rol',
            ],
            'activo' => ['nullable', 'boolean'],
        ]);

        $contrasenaOriginal = $validated['contrasena'];

        // Hash para la autenticación.
        $validated['contrasena'] = Hash::make($contrasenaOriginal);

        // Copia cifrada para consulta autorizada.
        $validated['contrasena_cifrada'] =
            Crypt::encryptString($contrasenaOriginal);

        $validated['activo'] = $request->boolean('activo');
        if (($validated['genero'] ?? null) !== 'Otro') {
            $validated['genero_detalle'] = null;
        }

        Usuario::create($validated);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Mostrar el detalle de un usuario.
     */
    public function show(string $id)
    {
        $usuario = Usuario::with('rol')->findOrFail($id);

        $contrasenaVisible = null;

        if ($usuario->contrasena_cifrada) {
            try {
                $contrasenaVisible = Crypt::decryptString(
                    $usuario->contrasena_cifrada
                );
            } catch (DecryptException $e) {
                $contrasenaVisible = null;
            }
        }

        return view('usuario.show', compact(
            'usuario',
            'contrasenaVisible'
        ));
    }

    /**
     * Mostrar el formulario para editar un usuario.
     */
    public function edit(string $id)
    {
        $usuario = Usuario::findOrFail($id);
        $roles = Rol::orderBy('nombre_rol')->get();

        return view('usuario.edit', [
            'usuario' => $usuario,
            'roles' => $roles,
            'tiposDocumento' => self::TIPOS_DOCUMENTO,
            'generos' => self::GENEROS,
        ]);
    }

    /**
     * Actualizar un usuario.
     */
    public function update(Request $request, string $id)
    {
        $usuario = Usuario::findOrFail($id);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'correo' => [
                'required',
                'email',
                'max:150',
                Rule::unique('usuarios', 'correo')
                    ->ignore($usuario->id),
            ],
            'telefono' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'genero' => ['nullable', 'string', Rule::in(self::GENEROS)],
            'genero_detalle' => ['nullable', 'string', 'max:100'],
            'tipo_documento' => [
                'nullable',
                'string',
                Rule::in(self::TIPOS_DOCUMENTO),
            ],
            'documento' => ['nullable', 'string', 'max:50'],
            'contrasena' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'id_rol' => [
                'required',
                'integer',
                'exists:roles,id_rol',
            ],
            'activo' => ['nullable', 'boolean'],
        ]);

        if (filled($validated['contrasena'] ?? null)) {
            $contrasenaOriginal = $validated['contrasena'];

            $validated['contrasena'] = Hash::make(
                $contrasenaOriginal
            );

            $validated['contrasena_cifrada'] =
                Crypt::encryptString($contrasenaOriginal);
        } else {
            unset($validated['contrasena']);
        }

        $validated['activo'] = $request->boolean('activo');
        if (($validated['genero'] ?? null) !== 'Otro') {
            $validated['genero_detalle'] = null;
        }
        $validated['fecha_actualizacion'] = now();

        $usuario->update($validated);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Generar una contraseña temporal.
     */
    public function restablecerContrasena(string $id)
    {
        $usuario = Usuario::findOrFail($id);

        $contrasenaTemporal = Str::password(12);


        $usuario->contrasena = Hash::make($contrasenaTemporal);

        $usuario->contrasena_cifrada =
            Crypt::encryptString($contrasenaTemporal);

        $usuario->fecha_actualizacion = now();

        $usuario->save();

        return redirect()
            ->route('usuarios.show', $usuario->id)
            ->with('mensaje', 'Contraseña restablecida correctamente.')
            ->with('contrasena_temporal', $contrasenaTemporal);
    }

    /**
     * Eliminar un usuario.
     */
    public function destroy(string $id)
    {
        Usuario::findOrFail($id)->delete();

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Rule;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{
    private const TIPOS_DOCUMENTO = [
        'CC',
        'CE',
        'TI',
        'PAS',
        'NIT',
    ];

    private const GENEROS = [
        'Masculino',
        'Femenino',
        'Otro',
    ];

    private function esAdministrador(): bool
    {
        return strtolower(
            auth()->user()?->rol?->nombre_rol ?? ''
        ) === 'administrador';
    }

    private function autorizarUsuario($id): void
    {
        if ($this->esAdministrador()) {
            return;
        }

        abort_unless(
            (string) $id === (string) auth()->id(),
            403
        );
    }

    public function index()
    {
        if ($this->esAdministrador()) {
            $usuarios = Usuario::with('rol')->paginate(15);
        } else {
            $usuarios = Usuario::with('rol')
                ->where('id', auth()->id())
                ->paginate(15);
        }

        return view('usuario.index', compact('usuarios'));
    }

    public function create()
    {
        $roles = Rol::orderBy('nombre_rol')->get();

        return view('usuario.create', [
            'roles' => $roles,
            'tiposDocumento' => self::TIPOS_DOCUMENTO,
            'generos' => self::GENEROS,
        ]);
    }

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
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'genero' => [
                'nullable',
                Rule::in(self::GENEROS),
            ],
            'genero_detalle' => [
                'nullable',
                'string',
                'max:100',
            ],
            'tipo_documento' => [
                'nullable',
                Rule::in(self::TIPOS_DOCUMENTO),
            ],
            'documento' => [
                'nullable',
                'string',
                'max:30',
                'unique:usuarios,documento',
            ],
            'contrasena' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'id_rol' => [
                'required',
                'exists:roles,id_rol',
            ],
            'activo' => ['nullable', 'boolean'],
        ]);

        $contrasenaOriginal = $validated['contrasena'];

        $validated['contrasena'] = Hash::make($contrasenaOriginal);
        $validated['contrasena_cifrada'] = Crypt::encryptString(
            $contrasenaOriginal
        );

        $validated['activo'] = $request->boolean('activo');

        if (($validated['genero'] ?? null) !== 'Otro') {
            $validated['genero_detalle'] = null;
        }

        Usuario::create($validated);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function show($id)
    {
        $this->autorizarUsuario($id);

        $usuario = Usuario::with('rol')->findOrFail($id);

        $contrasenaVisible = null;

        if (!empty($usuario->contrasena_cifrada)) {
            try {
                $contrasenaVisible = Crypt::decryptString(
                    $usuario->contrasena_cifrada
                );
            } catch (DecryptException $exception) {
                $contrasenaVisible = null;
            }
        }

        return view(
            'usuario.show',
            compact('usuario', 'contrasenaVisible')
        );
    }

    public function edit($id)
    {
        $this->autorizarUsuario($id);

        $usuario = Usuario::findOrFail($id);

        if ($this->esAdministrador()) {
            $roles = Rol::orderBy('nombre_rol')->get();
        } else {
            $roles = collect();
        }

        return view('usuario.edit', [
            'usuario' => $usuario,
            'roles' => $roles,
            'tiposDocumento' => self::TIPOS_DOCUMENTO,
            'generos' => self::GENEROS,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->autorizarUsuario($id);

        $usuario = Usuario::findOrFail($id);

        if ($this->esAdministrador()) {
            $validated = $request->validate([
                'nombre' => ['required', 'string', 'max:100'],
                'apellido' => ['required', 'string', 'max:100'],
                'correo' => [
                    'required',
                    'email',
                    'max:150',
                    Rule::unique('usuarios', 'correo')->ignore($usuario->id, 'id'),
                ],
                'telefono' => ['nullable', 'string', 'max:20'],
                'direccion' => ['nullable', 'string', 'max:255'],
                'genero' => [
                    'nullable',
                    Rule::in(self::GENEROS),
                ],
                'genero_detalle' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
                'tipo_documento' => [
                    'nullable',
                    Rule::in(self::TIPOS_DOCUMENTO),
                ],
                'documento' => [
                    'nullable',
                    'string',
                    'max:30',
                    Rule::unique('usuarios', 'documento')->ignore($usuario->id, 'id'),
                ],
                'contrasena' => [
                    'nullable',
                    'string',
                    'min:8',
                    'confirmed',
                ],
                'id_rol' => [
                    'required',
                    'exists:roles,id_rol',
                ],
                'activo' => ['nullable', 'boolean'],
            ]);

            if (!empty($validated['contrasena'])) {
                $contrasenaOriginal = $validated['contrasena'];

                $validated['contrasena'] = Hash::make(
                    $contrasenaOriginal
                );

                $validated['contrasena_cifrada'] = Crypt::encryptString(
                    $contrasenaOriginal
                );
            } else {
                unset($validated['contrasena']);
            }

            $validated['activo'] = $request->boolean('activo');
        } else {
            $validated = $request->validate([
                'nombre' => ['required', 'string', 'max:100'],
                'apellido' => ['required', 'string', 'max:100'],
                'correo' => [
                    'required',
                    'email',
                    'max:150',
                    Rule::unique('usuarios', 'correo')->ignore($usuario->id, 'id'),
                ],
                'telefono' => ['nullable', 'string', 'max:20'],
                'direccion' => ['nullable', 'string', 'max:255'],
                'genero' => [
                    'nullable',
                    Rule::in(self::GENEROS),
                ],
                'genero_detalle' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
                'tipo_documento' => [
                    'nullable',
                    Rule::in(self::TIPOS_DOCUMENTO),
                ],
                'documento' => [
                    'nullable',
                    'string',
                    'max:30',
                    Rule::unique('usuarios', 'documento')->ignore($usuario->id, 'id'),
                ],
            ]);
        }

        if (($validated['genero'] ?? null) !== 'Otro') {
            $validated['genero_detalle'] = null;
        }

        $validated['fecha_actualizacion'] = now();

        $usuario->update($validated);

        return redirect()
            ->route('usuarios.show', $usuario->id)
            ->with('success', 'Perfil actualizado correctamente.');
    }

    public function restablecerContrasena($id)
    {
        $usuario = Usuario::findOrFail($id);

        $contrasenaTemporal = Str::password(12);

        $usuario->contrasena = Hash::make($contrasenaTemporal);

        $usuario->contrasena_cifrada = Crypt::encryptString(
            $contrasenaTemporal
        );

        $usuario->fecha_actualizacion = now();

        $usuario->save();

        return redirect()
            ->route('usuarios.show', $usuario->id)
            ->with('success', 'Contraseña restablecida correctamente.')
            ->with('contrasenaTemporal', $contrasenaTemporal);
    }

    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);

        $usuario->delete();

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}

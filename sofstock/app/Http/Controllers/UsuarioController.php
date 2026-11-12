<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function account(Request $request)
    {
        return view('usuario.account', ['usuario' => $request->user()]);
    }

    private function esAdministrador(): bool
    {
        return auth()->user()?->esAdministrador() ?? false;
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

    private function autorizarAdministrador(): void
    {
        abort_unless($this->esAdministrador(), 403);
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
        $this->autorizarAdministrador();

        $roles = Rol::query()
            ->whereRaw('LOWER(TRIM(nombre_rol)) NOT IN (?, ?, ?)', ['administrador', 'pendiente', 'secretaria'])
            ->orderBy('nombre_rol')
            ->get();

        return view('usuario.create', [
            'roles' => $roles,
            'tiposDocumento' => Usuario::TIPOS_DOCUMENTO,
            'generos' => Usuario::GENEROS,
        ]);
    }

    public function store(Request $request)
    {
        $this->autorizarAdministrador();

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
                Rule::in(Usuario::GENEROS),
            ],
            'genero_detalle' => [
                'nullable',
                'string',
                'max:100',
            ],
            'tipo_documento' => [
                'nullable',
                Rule::in(Usuario::TIPOS_DOCUMENTO),
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
                Rule::exists('roles', 'id_rol')->where(
                    fn ($query) => $query->whereRaw(
                        'LOWER(TRIM(nombre_rol)) NOT IN (?, ?, ?)',
                        ['administrador', 'pendiente', 'secretaria']
                    )
                ),
            ],
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

        if (! empty($usuario->contrasena_cifrada)) {
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
            $roles = Rol::query()
                ->whereRaw('LOWER(TRIM(nombre_rol)) NOT IN (?, ?, ?)', ['administrador', 'pendiente', 'secretaria'])
                ->orderBy('nombre_rol')
                ->get();

            if ($usuario->esAdministrador() && $usuario->rol) {
                $roles->prepend($usuario->rol);
            }
        } else {
            $roles = collect();
        }

        return view('usuario.edit', [
            'usuario' => $usuario,
            'roles' => $roles,
            'tiposDocumento' => Usuario::TIPOS_DOCUMENTO,
            'generos' => Usuario::GENEROS,
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
                    Rule::in(Usuario::GENEROS),
                ],
                'genero_detalle' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
                'tipo_documento' => [
                    'nullable',
                    Rule::in(Usuario::TIPOS_DOCUMENTO),
                ],
                'documento' => [
                    'nullable',
                    'string',
                    'max:30',
                    Rule::unique('usuarios', 'documento')->ignore($usuario->id, 'id'),
                ],
                'id_rol' => [
                    'required',
                    $usuario->esAdministrador()
                        ? Rule::in([$usuario->id_rol])
                        : Rule::exists('roles', 'id_rol')->where(
                            fn ($query) => $query->whereRaw(
                                'LOWER(TRIM(nombre_rol)) NOT IN (?, ?, ?)',
                                ['administrador', 'pendiente', 'secretaria']
                            )
                        ),
                ],
                'activo' => ['required', 'boolean'],
            ]);

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
                    Rule::in(Usuario::GENEROS),
                ],
                'genero_detalle' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
                'tipo_documento' => [
                    'nullable',
                    Rule::in(Usuario::TIPOS_DOCUMENTO),
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
        $estadoActivoSolicitado = $validated['activo'] ?? $usuario->activo;

        $actualizado = DB::transaction(function () use (
            $usuario,
            $validated,
            $estadoActivoSolicitado
        ): bool {
            $usuarioBloqueado = Usuario::query()
                ->lockForUpdate()
                ->findOrFail($usuario->id);

            if (
                $usuarioBloqueado->esAdministrador() &&
                $usuarioBloqueado->activo &&
                ! $estadoActivoSolicitado &&
                (
                    (int) $usuarioBloqueado->id === (int) auth()->id() ||
                    ! Usuario::administradores()
                        ->where('activo', true)
                        ->where('usuarios.id', '!=', $usuarioBloqueado->id)
                        ->lockForUpdate()
                        ->exists()
                )
            ) {
                return false;
            }

            $usuarioBloqueado->update($validated);

            return true;
        });

        if (! $actualizado) {
            return back()
                ->withErrors([
                    'activo' => (int) $usuario->id === (int) auth()->id()
                        ? 'Para desactivar tu propia cuenta, usa la opción “Mi cuenta”.'
                        : 'No se puede desactivar al único administrador activo.',
                ])
                ->withInput();
        }

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
        $this->autorizarAdministrador();
        $usuario = Usuario::findOrFail($id);

        if ($usuario->esAdministrador()) {
            if (Usuario::administradores()->count() <= 1) {
                return redirect()->route('usuarios.index')
                    ->with('error', 'No se puede eliminar al único administrador del sistema.');
            }
        }

        $usuario->delete();

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    public function deactivateOwnAccount(Request $request): RedirectResponse
    {
        $request->validate([
            'contrasena_actual' => ['required', 'current_password:web'],
            'confirmar_desactivacion' => ['required', 'accepted'],
        ]);

        $usuario = $request->user();
        $desactivado = DB::transaction(function () use ($usuario): bool {
            $usuario = Usuario::query()->lockForUpdate()->findOrFail($usuario->id);
            $administradores = Usuario::administradores()
                ->where('activo', true)
                ->where('usuarios.id', '!=', $usuario->id)
                ->lockForUpdate()
                ->get(['usuarios.id']);

            if ($administradores->isEmpty()) {
                return false;
            }

            $usuario->forceFill([
                'activo' => false,
                'fecha_actualizacion' => now(),
            ])->save();

            $notificacion = Notificacion::create([
                'usuario_emisor_id' => $usuario->id,
                'titulo' => 'Usuario desactivó su cuenta',
                'mensaje' => "{$usuario->nombre} {$usuario->apellido} ({$usuario->correo}) desactivó su cuenta. Un administrador puede reactivarla desde Usuarios.",
                'tipo' => Notificacion::TIPO_CUENTA_DESACTIVADA,
                'datos' => ['usuario_id' => $usuario->id],
            ]);
            $notificacion->usuariosDestinatarios()->sync($administradores->modelKeys());

            return true;
        });

        if (! $desactivado) {
            return back()->withErrors([
                'contrasena_actual' => 'No se puede desactivar la cuenta porque no hay otro administrador activo que pueda reactivarla.',
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Tu cuenta quedó desactivada. Un administrador puede reactivarla.');
    }
}

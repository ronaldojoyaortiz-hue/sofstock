<?php

namespace App\Http\Controllers;

use App\Models\Rol as LegacyRole;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InitialAdminController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Usuario::exists()) {
            return redirect()
                ->route('login')
                ->with('success', 'La configuración inicial ya se completó. Inicia sesión para continuar.');
        }

        return view('auth.initial-admin');
    }

    public function store(Request $request): RedirectResponse
    {
        return Cache::lock('sofstock:initial-admin-setup', 30)->block(10, function () use ($request): RedirectResponse {
            if (Usuario::exists()) {
                return redirect()
                    ->route('login')
                    ->with('success', 'La configuración inicial ya se completó. Inicia sesión para continuar.');
            }

            $validated = $request->validate([
                'nombre' => ['required', 'string', 'max:100'],
                'apellido' => ['required', 'string', 'max:100'],
                'correo' => ['required', 'email', 'max:150', 'unique:usuarios,correo'],
                'contrasena' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $usuario = DB::transaction(function () use ($validated): Usuario {
                $legacyRole = LegacyRole::query()
                    ->whereRaw('LOWER(nombre_rol) = ?', ['administrador'])
                    ->first();

                if (! $legacyRole) {
                    $legacyRole = LegacyRole::create([
                        'nombre_rol' => 'administrador',
                        'descripcion' => 'Administrador del sistema',
                    ]);
                }

                $administratorRole = Role::findOrCreate('administrador', 'web');
                $administratorRole->syncPermissions(
                    Permission::query()->where('guard_name', 'web')->get()
                );

                $usuario = Usuario::create([
                    'nombre' => $validated['nombre'],
                    'apellido' => $validated['apellido'],
                    'correo' => $validated['correo'],
                    'contrasena' => Hash::make($validated['contrasena']),
                    'id_rol' => $legacyRole->id_rol,
                    'activo' => true,
                ]);
                $usuario->assignRole($administratorRole);

                return $usuario;
            });

            Auth::login($usuario);
            $request->session()->regenerate();

            return redirect()->route('security-question.setup');
        });
    }
}

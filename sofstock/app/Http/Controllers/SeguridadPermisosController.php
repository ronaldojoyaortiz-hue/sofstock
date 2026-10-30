<?php

namespace App\Http\Controllers;

use App\Models\Rol as LegacyRole;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SeguridadPermisosController extends Controller
{
    public function index(Request $request): View
    {
        return $this->roles($request);
    }

    public function assignments(): View
    {
        $assignments = Role::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'administrador')
            ->with(['permissions' => fn ($query) => $query->where('guard_name', 'web')])
            ->orderBy('id')
            ->get()
            ->flatMap(fn (Role $role) => $role->permissions->map(fn (Permission $permission) => (object) [
                'role' => $role,
                'permission' => $permission,
            ]))
            ->sortBy([
                ['role.id', 'asc'],
                ['permission.name', 'asc'],
            ])
            ->values();

        return view('seguridad.permisos.index', compact('assignments'));
    }

    public function showRole(int $role): View
    {
        $role = Role::query()
            ->where('guard_name', 'web')
            ->with([
                'permissions' => fn ($query) => $query->where('guard_name', 'web')->orderBy('name'),
                'users',
            ])
            ->findOrFail($role);
        abort_if($role->name === 'administrador', 404);
        $legacyRole = LegacyRole::query()
            ->with('usuarios')
            ->where('nombre_rol', $role->name)
            ->first();
        $users = $role->users
            ->merge($legacyRole?->usuarios ?? new EloquentCollection)
            ->unique('id')
            ->values();
        $permissions = $role->permissions
            ->groupBy(fn (Permission $permission) => Str::afterLast($permission->name, ' '));

        return view('seguridad.permisos.role-show', compact('role', 'legacyRole', 'users', 'permissions'));
    }

    public function destroyRole(int $role): RedirectResponse
    {
        $role = Role::query()->where('guard_name', 'web')->findOrFail($role);

        if ($role->name === 'administrador') {
            return redirect()->route('seguridad.permisos.index')
                ->with('error', 'El rol administrador está protegido y no se puede eliminar.');
        }

        $legacyRole = LegacyRole::query()
            ->where('nombre_rol', $role->name)
            ->first();

        if ($role->users()->exists() || $legacyRole?->usuarios()->exists()) {
            return redirect()->route('seguridad.permisos.index')
                ->with('error', "No se puede eliminar {$role->name} porque todavía tiene usuarios asignados.");
        }

        DB::transaction(function () use ($role, $legacyRole): void {
            $role->delete();
            $legacyRole?->delete();
        });

        return redirect()->route('seguridad.permisos.index')
            ->with('success', "El rol {$role->name} fue eliminado.");
    }

    public function showAssignment(int $role, int $permission): View
    {
        $role = Role::query()->where('guard_name', 'web')->findOrFail($role);
        abort_if($role->name === 'administrador', 404);
        $permission = $this->findWebPermission($permission);
        abort_unless($role->hasPermissionTo($permission), 404);

        return view('seguridad.permisos.show', compact('role', 'permission'));
    }

    public function destroyAssignment(int $role, int $permission): RedirectResponse
    {
        $role = Role::query()->where('guard_name', 'web')->findOrFail($role);
        abort_if($role->name === 'administrador', 404);
        $permission = $this->findWebPermission($permission);
        abort_unless($role->hasPermissionTo($permission), 404);
        DB::transaction(function () use ($role, $permission): void {
            $role->permissions()->detach($permission->getKey());
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('seguridad.permisos.assignments')
            ->with('success', "Se quitó «{$permission->name}» del rol {$role->name}.");
    }

    public function roles(Request $request): View
    {
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => Str::afterLast($permission->name, ' '));
        $roles = Role::query()
            ->with(['permissions' => fn ($query) => $query->where('guard_name', 'web')])
            ->where('guard_name', 'web')
            ->where('name', '!=', 'administrador')
            ->orderBy('name')
            ->get();
        $selectedRole = $roles->firstWhere('id', (int) $request->query('role_id'))
            ?? $roles->firstWhere('name', 'socios')
            ?? $roles->first();
        $showMatrix = $request->filled('role_id') || $request->boolean('gestionar');
        $canManage = $request->boolean('gestionar');

        return view('seguridad.permisos.roles', compact('permissions', 'roles', 'selectedRole', 'showMatrix', 'canManage'));
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        abort_unless($role->guard_name === 'web', 404);

        if ($role->name === 'administrador') {
            return redirect()->route('seguridad.permisos.roles')
                ->with('error', 'Los permisos del rol administrador están protegidos.');
        }

        $validated = $request->validate([
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [
                'integer',
                'distinct',
                Rule::exists('permissions', 'id')->where('guard_name', 'web'),
            ],
        ]);

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('id', $validated['permissions'] ?? [])
            ->get();

        $role->syncPermissions($permissions);

        return redirect()->route('seguridad.permisos.roles', ['role_id' => $role->id, 'gestionar' => 1])
            ->with('success', "Permisos actualizados para {$role->name}.");
    }

    private function findWebPermission(int $id): Permission
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->findOrFail($id);
    }
}

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
    private const HIDDEN_PERMISSIONS = ['gestionar seguridad'];

    public function index(Request $request): View
    {
        return $this->roles($request);
    }

    public function assignments(): View
    {
        $assignments = Role::query()
            ->where('guard_name', 'web')
            ->whereRaw('LOWER(TRIM(name)) != ?', ['administrador'])
            ->with(['permissions' => fn ($query) => $query
                ->where('guard_name', 'web')
                ->whereNotIn('name', self::HIDDEN_PERMISSIONS)])
            ->orderBy('id')
            ->get()
            ->flatMap(fn (Role $role) => $role->permissions->map(fn (Permission $permission) => (object) [
                'role' => $role,
                'permission' => $permission,
                'permissionLabel' => $this->nombreVisiblePermiso($permission),
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
                'permissions' => fn ($query) => $query
                    ->where('guard_name', 'web')
                    ->whereNotIn('name', self::HIDDEN_PERMISSIONS)
                    ->orderBy('name'),
                'users',
            ])
            ->findOrFail($role);
        abort_if(mb_strtolower(trim($role->name)) === 'administrador', 404);

        $legacyRole = LegacyRole::query()
            ->with('usuarios')
            ->where('nombre_rol', $role->name)
            ->first();
        $users = $role->users
            ->merge($legacyRole?->usuarios ?? new EloquentCollection)
            ->unique('id')
            ->values();
        $permissions = $role->permissions
            ->groupBy(fn (Permission $permission) => $this->moduloVisiblePermiso($permission));
        $permissionLabels = $role->permissions
            ->mapWithKeys(fn (Permission $permission) => [$permission->id => $this->nombreVisiblePermiso($permission)]);

        return view('seguridad.permisos.role-show', compact('role', 'legacyRole', 'users', 'permissions', 'permissionLabels'));
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
        abort_if(mb_strtolower(trim($role->name)) === 'administrador', 404);
        $permission = $this->findWebPermission($permission);
        abort_unless($role->hasPermissionTo($permission), 404);
        $permissionLabel = $this->nombreVisiblePermiso($permission);

        return view('seguridad.permisos.show', compact('role', 'permission', 'permissionLabel'));
    }

    public function destroyAssignment(int $role, int $permission): RedirectResponse
    {
        $role = Role::query()->where('guard_name', 'web')->findOrFail($role);
        abort_if(mb_strtolower(trim($role->name)) === 'administrador', 404);
        $permission = $this->findWebPermission($permission);
        abort_unless($role->hasPermissionTo($permission), 404);
        $permissionLabel = $this->nombreVisiblePermiso($permission);
        DB::transaction(function () use ($role, $permission): void {
            $role->permissions()->detach($permission->getKey());
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('seguridad.permisos.assignments')
            ->with('success', "Se quitó «{$permissionLabel}» del rol {$role->name}.");
    }

    public function roles(Request $request): View
    {
        LegacyRole::query()
            ->whereRaw('LOWER(TRIM(nombre_rol)) != ?', ['secretaria'])
            ->orderBy('id_rol')
            ->get()
            ->each(fn (LegacyRole $legacyRole) => Role::findOrCreate($legacyRole->nombre_rol, 'web'));

        $allPermissions = Permission::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', self::HIDDEN_PERMISSIONS)
            ->orderBy('name')
            ->get()
            ->values();
        $permissionLabels = $allPermissions
            ->mapWithKeys(fn (Permission $permission) => [$permission->id => $this->nombreVisiblePermiso($permission)]);
        $permissions = $allPermissions
            ->groupBy(fn (Permission $permission) => $this->moduloVisiblePermiso($permission))
            ->map(function ($modulePermissions, string $module): array {
                $knownActions = ['acceder', 'ver', 'crear', 'editar', 'eliminar'];
                $actions = collect($knownActions)->mapWithKeys(function (string $action) use ($module, $modulePermissions): array {
                    $permission = $modulePermissions->first(
                        fn (Permission $candidate): bool => $candidate->name === "{$action} {$module}"
                    );

                    return [$action => $permission];
                });
                $knownPermissionIds = $actions->filter()->map(fn (Permission $permission) => $permission->id);

                return [
                    'label' => $this->nombreVisibleModulo($module),
                    'actions' => $actions,
                    'other' => $modulePermissions
                        ->reject(fn (Permission $permission) => $knownPermissionIds->contains($permission->id))
                        ->values(),
                ];
            });
        $roles = Role::query()
            ->with(['permissions' => fn ($query) => $query
                ->where('guard_name', 'web')
                ->whereNotIn('name', self::HIDDEN_PERMISSIONS)])
            ->where('guard_name', 'web')
            ->whereRaw('LOWER(TRIM(name)) != ?', ['administrador'])
            ->orderBy('name')
            ->get();
        $selectedRole = $roles->firstWhere('id', (int) $request->query('role_id'))
            ?? $roles->firstWhere('name', 'socios')
            ?? $roles->first();
        $showMatrix = $request->filled('role_id') || $request->boolean('gestionar');
        $canManage = $request->boolean('gestionar');

        return view('seguridad.permisos.roles', compact('permissions', 'permissionLabels', 'roles', 'selectedRole', 'showMatrix', 'canManage'));
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        abort_unless($role->guard_name === 'web', 404);

        if (mb_strtolower(trim($role->name)) === 'administrador') {
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
            ->whereNotIn('name', self::HIDDEN_PERMISSIONS)
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

    private function nombreVisiblePermiso(Permission $permission): string
    {
        [$action, $module] = array_pad(explode(' ', $permission->name, 2), 2, null);
        $moduleLabel = $module ? $this->nombreVisibleModulo($module) : null;
        $actionLabel = match ($action) {
            'acceder' => 'Ver módulo',
            'ver' => 'Leer datos',
            'crear' => 'Crear',
            'editar' => 'Modificar',
            'eliminar' => 'Eliminar',
            default => null,
        };

        return $actionLabel && $moduleLabel
            ? "{$actionLabel} de {$moduleLabel}"
            : ucfirst($permission->name);
    }

    private function moduloVisiblePermiso(Permission $permission): string
    {
        return Str::afterLast($permission->name, ' ');
    }

    private function nombreVisibleModulo(string $module): string
    {
        return match ($module) {
            'reports' => 'Reportes',
            'settings' => 'Configuración',
            'inmuebles' => 'Inmuebles',
            'detallefacturas' => 'Detalles de factura',
            'categorias' => 'Categorías',
            'notificaciones' => 'Notificaciones',
            'auditorias' => 'Auditorías',
            'dashboard' => 'Inicio',
            'seguridad' => 'Seguridad',
            default => ucfirst($module),
        };
    }
}

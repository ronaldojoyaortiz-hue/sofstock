<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const MODULES = [
        'dashboard',
        'usuarios',
        'roles',
        'categorias',
        'productos',
        'facturas',
        'proveedores',
        'inmuebles',
        'contratos',
        'actas',
        'auditorias',
        'reportes',
        'settings',
        'notificaciones',
    ];

    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $permissionPivot = $columnNames['permission_pivot_key'] ?: 'permission_id';
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';

        foreach (self::MODULES as $module) {
            $accessPermission = Permission::findOrCreate("acceder {$module}", 'web');
            $existingPermissionIds = Permission::query()
                ->where('guard_name', 'web')
                ->where('name', 'like', "% {$module}")
                ->where('name', '!=', $accessPermission->name)
                ->pluck('id');

            if ($existingPermissionIds->isEmpty()) {
                continue;
            }

            Role::query()
                ->where('guard_name', 'web')
                ->whereHas('permissions', fn ($query) => $query->whereIn('permissions.id', $existingPermissionIds))
                ->get()
                ->each(fn (Role $role) => $role->givePermissionTo($accessPermission));

            DB::table($tableNames['model_has_permissions'])
                ->whereIn($permissionPivot, $existingPermissionIds)
                ->get([
                    $modelMorphKey,
                    'model_type',
                ])
                ->unique(fn (object $assignment): string => $assignment->model_type . ':' . $assignment->{$modelMorphKey})
                ->each(function (object $assignment) use ($accessPermission, $tableNames, $permissionPivot, $modelMorphKey): void {
                    DB::table($tableNames['model_has_permissions'])->insertOrIgnore([
                        $permissionPivot => $accessPermission->getKey(),
                        $modelMorphKey => $assignment->{$modelMorphKey},
                        'model_type' => $assignment->model_type,
                    ]);
                });
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep access assignments because administrators may have adjusted them.
    }
};

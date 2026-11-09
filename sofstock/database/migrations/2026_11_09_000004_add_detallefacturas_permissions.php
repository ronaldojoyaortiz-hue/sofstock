<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const ACTIONS = ['acceder', 'ver', 'crear', 'editar', 'eliminar'];

    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $permissionPivot = $columnNames['permission_pivot_key'] ?: 'permission_id';
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';

        foreach (self::ACTIONS as $action) {
            $permission = Permission::findOrCreate("{$action} detallefacturas", 'web');
            $sourcePermissions = Permission::query()
                ->where('guard_name', 'web')
                ->whereIn('name', ["{$action} facturas", "{$action} detallefacturas"])
                ->where('id', '!=', $permission->getKey())
                ->get();

            foreach ($sourcePermissions as $sourcePermission) {
                Role::query()
                    ->where('guard_name', 'web')
                    ->whereHas('permissions', fn ($query) => $query->where('permissions.id', $sourcePermission->getKey()))
                    ->get()
                    ->each(fn (Role $role) => $role->givePermissionTo($permission));

                DB::table($tableNames['model_has_permissions'])
                    ->where($permissionPivot, $sourcePermission->getKey())
                    ->get([$modelMorphKey, 'model_type'])
                    ->unique(fn (object $assignment): string => $assignment->model_type.':'.$assignment->{$modelMorphKey})
                    ->each(function (object $assignment) use ($permission, $tableNames, $permissionPivot, $modelMorphKey): void {
                        DB::table($tableNames['model_has_permissions'])->insertOrIgnore([
                            $permissionPivot => $permission->getKey(),
                            $modelMorphKey => $assignment->{$modelMorphKey},
                            'model_type' => $assignment->model_type,
                        ]);
                    });
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Keep permissions and assignments because administrators may have adjusted them.
    }
};

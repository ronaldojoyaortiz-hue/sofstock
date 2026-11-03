<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $permissionNames = [];

        foreach (['ver', 'crear', 'editar', 'eliminar'] as $action) {
            $old = Permission::query()
                ->where('guard_name', 'web')
                ->where('name', "{$action} reports")
                ->first();
            $new = Permission::findOrCreate("{$action} reportes", 'web');

            if ($old && $old->id !== $new->id) {
                $old->roles->each(fn (Role $role) => $role->givePermissionTo($new));
                $this->moverAsignacionesDirectas($old->id, $new->id);
                $old->delete();
            }

            $permissionNames[] = $new;
        }

        Role::query()
            ->where('guard_name', 'web')
            ->where('name', 'administrador')
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permissionNames));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function moverAsignacionesDirectas(int $permisoAnterior, int $permisoNuevo): void
    {
        $tabla = config('permission.table_names.model_has_permissions', 'model_has_permissions');
        $columnaModelo = config('permission.column_names.model_morph_key', 'model_id');

        if (! DB::getSchemaBuilder()->hasTable($tabla)) {
            return;
        }

        $asignaciones = DB::table($tabla)->where('permission_id', $permisoAnterior)->get();

        foreach ($asignaciones as $asignacion) {
            $condiciones = [
                'permission_id' => $permisoNuevo,
                $columnaModelo => $asignacion->{$columnaModelo},
                'model_type' => $asignacion->model_type,
            ];

            if (! DB::table($tabla)->where($condiciones)->exists()) {
                DB::table($tabla)->insert($condiciones);
            }
        }
    }

    public function down(): void
    {
        // Se conservan los permisos localizados para no revocar accesos existentes.
    }
};

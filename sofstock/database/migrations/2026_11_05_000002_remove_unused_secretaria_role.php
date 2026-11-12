<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $role = DB::table('roles')
            ->whereRaw('LOWER(TRIM(nombre_rol)) = ?', ['secretaria'])
            ->first(['id_rol']);

        if ($role) {
            $hasLegacyUsers = DB::table('usuarios')
                ->where('id_rol', $role->id_rol)
                ->exists();

            if (! $hasLegacyUsers) {
                DB::table('roles')->where('id_rol', $role->id_rol)->delete();
            }
        }

        $roleTable = config('permission.table_names.roles');
        $modelHasRolesTable = config('permission.table_names.model_has_roles');
        $permissionRole = DB::table($roleTable)
            ->whereRaw('LOWER(TRIM(name)) = ?', ['secretaria'])
            ->where('guard_name', 'web')
            ->first(['id']);
        $hasPermissionUsers = $permissionRole && DB::table($modelHasRolesTable)
            ->where('role_id', $permissionRole->id)
            ->exists();

        if ($permissionRole && ! $hasPermissionUsers) {
            DB::table($roleTable)->where('id', $permissionRole->id)->delete();
        }
    }

    public function down(): void
    {
        if (! DB::table('roles')->whereRaw('LOWER(TRIM(nombre_rol)) = ?', ['secretaria'])->exists()) {
            DB::table('roles')->insert([
                'nombre_rol' => 'secretaria',
                'descripcion' => 'es la secretaria de la junta directiva',
            ]);
        }
    }
};

<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SeguridadSeeder extends Seeder
{
    public function run(): void
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $permissionRegistrar->forgetCachedPermissions();

        $permissionsByModule = [
            'seguridad' => ['gestionar'],
            'dashboard' => ['ver'],
            'usuarios' => ['ver', 'crear', 'editar', 'eliminar'],
            'roles' => ['ver', 'crear', 'editar', 'eliminar'],
            'categorias' => ['ver', 'crear', 'editar', 'eliminar'],
            'productos' => ['ver', 'crear', 'editar', 'eliminar'],
            'facturas' => ['ver', 'crear', 'editar', 'eliminar'],
            'proveedores' => ['ver', 'crear', 'editar', 'eliminar'],
            'inmuebles' => ['ver', 'crear', 'editar', 'eliminar'],
            'contratos' => ['ver', 'crear', 'editar', 'eliminar'],
            'auditorias' => ['ver'],
        ];

        foreach ($permissionsByModule as $module => $actions) {
            foreach ($actions as $action) {
                Permission::findOrCreate("{$action} {$module}", 'web');
            }
        }

        $administrator = Role::findOrCreate('administrador', 'web');
        $administrator->syncPermissions(
            Permission::query()->where('guard_name', 'web')->get()
        );

        DB::table('roles')
            ->select(['id_rol', 'nombre_rol'])
            ->orderBy('id_rol')
            ->get()
            ->each(function (object $legacyRole): void {
                $role = Role::findOrCreate($legacyRole->nombre_rol, 'web');

                Usuario::query()
                    ->where('id_rol', $legacyRole->id_rol)
                    ->get()
                    ->each(fn (Usuario $usuario) => $usuario->syncRoles([$role]));
            });

        $permissionRegistrar->forgetCachedPermissions();
    }
}

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
            'dashboard' => ['acceder', 'ver'],
            'usuarios' => ['acceder', 'ver', 'crear', 'editar', 'eliminar'],
            'roles' => ['acceder', 'ver', 'crear', 'editar', 'eliminar'],
            'categorias' => ['acceder', 'ver', 'crear', 'editar', 'eliminar'],
            'productos' => ['acceder', 'ver', 'crear', 'editar', 'eliminar'],
            'facturas' => ['acceder', 'ver', 'crear', 'editar', 'eliminar'],
            'detallefacturas' => ['acceder', 'ver', 'crear', 'editar', 'eliminar'],
            'proveedores' => ['acceder', 'ver', 'crear', 'editar', 'eliminar'],
            'inmuebles' => ['acceder', 'ver', 'crear', 'editar', 'eliminar'],
            'contratos' => ['acceder', 'ver', 'crear', 'editar', 'eliminar'],
            'actas' => ['acceder', 'ver', 'crear', 'editar', 'eliminar'],
            'auditorias' => ['acceder', 'ver', 'eliminar'],
            'reportes' => ['acceder', 'ver', 'crear', 'editar', 'eliminar'],
            'settings' => ['acceder', 'ver', 'editar'],
            'notificaciones' => ['acceder', 'ver', 'crear', 'eliminar'],
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
                    ->each(fn(Usuario $usuario) => $usuario->syncRoles([$role]));
            });

        $permissionRegistrar->forgetCachedPermissions();
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = collect(['ver', 'crear', 'eliminar'])
            ->map(fn (string $action) => Permission::findOrCreate("{$action} notificaciones", 'web'));

        Role::query()
            ->where('guard_name', 'web')
            ->where('name', 'administrador')
            ->get()
            ->each(function (Role $role) use ($permissions): void {
                foreach ($permissions as $permission) {
                    $role->givePermissionTo($permission);
                }
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Conserva permisos y asignaciones que pueden haberse configurado después.
    }
};

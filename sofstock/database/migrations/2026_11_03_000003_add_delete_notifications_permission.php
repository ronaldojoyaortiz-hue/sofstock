<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::findOrCreate('eliminar notificaciones', 'web');

        Role::query()
            ->where('guard_name', 'web')
            ->where('name', 'administrador')
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permission));
    }

    public function down(): void
    {
        Permission::query()
            ->where('guard_name', 'web')
            ->where('name', 'eliminar notificaciones')
            ->get()
            ->each(fn (Permission $permission) => $permission->delete());
    }
};

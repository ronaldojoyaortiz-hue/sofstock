<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = collect(['ver', 'editar'])
            ->map(fn (string $action) => Permission::findOrCreate("{$action} settings", 'web'));

        Role::query()
            ->where('guard_name', 'web')
            ->where('name', 'administrador')
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permissions->all()));
    }

    public function down(): void
    {
        Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['ver settings', 'editar settings'])
            ->get()
            ->each(fn (Permission $permission) => $permission->delete());
    }
};

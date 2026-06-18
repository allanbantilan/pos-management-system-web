<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::query()
            ->where('name', 'backend-operator')
            ->where('guard_name', 'backend')
            ->first();

        $permission = Permission::query()
            ->where('name', 'can view dashboard analytics')
            ->where('guard_name', 'backend')
            ->first();

        if ($role && $permission) {
            $role->revokePermissionTo($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Intentionally left empty to avoid restoring report access after removal.
    }
};

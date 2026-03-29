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

        $permissions = [
            'can view audit log',
            'can export data',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'backend',
            ]);
        }

        $backendAdminRole = Role::query()
            ->where('name', 'backend-admin')
            ->where('guard_name', 'backend')
            ->first();

        if ($backendAdminRole) {
            $backendAdminRole->givePermissionTo($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Intentionally left empty to avoid removing permissions that may already be in active use.
    }
};

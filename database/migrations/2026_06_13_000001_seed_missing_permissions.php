<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'can view dashboard analytics',
            'can access backend panel',
            'can manage system settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'backend',
            ]);
        }

        $adminRole = Role::where('name', 'backend-admin')
            ->where('guard_name', 'backend')
            ->first();

        if ($adminRole) {
            $adminRole->syncPermissions($permissions);
        }
    }

    public function down(): void
    {
        Permission::where('guard_name', 'backend')
            ->whereIn('name', [
                'can view dashboard analytics',
                'can access backend panel',
                'can manage system settings',
            ])
            ->delete();
    }
};

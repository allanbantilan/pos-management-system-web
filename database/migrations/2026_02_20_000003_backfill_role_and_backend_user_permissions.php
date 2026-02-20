<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $webRolePermissions = $this->modelPermissions('role');
        $backendRolePermissions = $this->modelPermissions('role');
        $backendUserPermissions = $this->modelPermissions('backend user');

        foreach ($webRolePermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        foreach (array_merge($backendRolePermissions, $backendUserPermissions) as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'backend',
            ]);
        }

        $clientAdminRole = Role::query()
            ->where('name', 'client-admin')
            ->where('guard_name', 'web')
            ->first();
        if ($clientAdminRole) {
            $clientAdminRole->givePermissionTo($webRolePermissions);
        }

        $backendAdminRole = Role::query()
            ->where('name', 'backend-admin')
            ->where('guard_name', 'backend')
            ->first();
        if ($backendAdminRole) {
            $backendAdminRole->givePermissionTo(array_merge($backendRolePermissions, $backendUserPermissions));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left empty to avoid removing permissions that may already be in active use.
    }

    /**
     * @return array<int, string>
     */
    private function modelPermissions(string $model): array
    {
        return [
            "can view {$model}",
            "can create {$model}",
            "can update {$model}",
            "can delete {$model}",
            "can force delete {$model}",
            "can restore {$model}",
            "can replicate {$model}",
            "can reorder {$model}",
        ];
    }
};

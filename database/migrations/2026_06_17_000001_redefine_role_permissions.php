<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Redefine the backend role permission matrix and guarantee the default web
 * employee role exists. Not production-guarded: roles/permissions are
 * infrastructure (test *users* are still seeded only outside production).
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $backendRoles = [
            'backend-operator' => [
                'can access backend panel',
                ...$this->crud('pos item'),
                ...$this->crud('pos category'),
                'can view dashboard analytics', // enables Transactions, Receipts, Statistics
            ],
            'backend-analyst' => [
                'can access backend panel',
                'can view dashboard analytics',
                'can view pos item',
                'can view pos category',
                'can view user',
                'can view backend user',
                'can view role',
                'can view audit log',
                'can export reports',
                'can export data',
            ],
            'backend-support' => [
                'can access backend panel',
                'can view user',
                'can view backend user',
                'can view dashboard analytics', // view transactions/receipts
            ],
        ];

        foreach ($backendRoles as $roleName => $permissions) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'backend']);
            }

            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'backend']);
            $role->syncPermissions($permissions);
        }

        // Super-admin gets every backend permission (belt-and-suspenders next to
        // the Gate::before bypass, so it still works if the gate is removed).
        $adminRole = Role::firstOrCreate(['name' => 'backend-admin', 'guard_name' => 'backend']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'backend')->get());

        // Default web employee role: must exist everywhere so newly created
        // client users can be auto-assigned to it.
        $cashierPermissions = [
            'can view pos item',
            'can view pos category',
            'can process sale',
            'can apply discount',
            'can open shift',
            'can close shift',
        ];
        foreach ($cashierPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web'])
            ->syncPermissions($cashierPermissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Permissions may be in active use; intentionally not reverted.
    }

    /**
     * @return array<int, string>
     */
    private function crud(string $model): array
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

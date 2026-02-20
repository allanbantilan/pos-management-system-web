<?php

use App\Models\BackendUser;
use App\Models\User;
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

        $webPermissions = array_values(array_unique([
            ...$this->modelPermissions('user'),
            ...$this->modelPermissions('pos item'),
            ...$this->modelPermissions('pos category'),
            ...$this->modelPermissions('role'),
            'can process sale',
            'can apply discount',
            'can void sale',
            'can open shift',
            'can close shift',
            'can view sales report',
            'can refund sale',
        ]));

        $backendPermissions = [
            ...$this->modelPermissions('backend user'),
            ...$this->modelPermissions('pos item'),
            ...$this->modelPermissions('pos category'),
            ...$this->modelPermissions('user'),
            ...$this->modelPermissions('role'),
            'can access backend panel',
            'can view dashboard analytics',
            'can export reports',
            'can manage system settings',
            'can manage integrations',
        ];
        $backendPermissions = array_values(array_unique($backendPermissions));

        foreach ($webPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        foreach ($backendPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'backend',
            ]);
        }

        $webRoles = [
            'client-admin' => $webPermissions,
            'store-manager' => [
                ...$this->modelPermissions('pos item'),
                ...$this->modelPermissions('pos category'),
                'can process sale',
                'can apply discount',
                'can void sale',
                'can open shift',
                'can close shift',
                'can view sales report',
                'can refund sale',
            ],
            'cashier' => [
                'can view pos item',
                'can view pos category',
                'can process sale',
                'can apply discount',
                'can open shift',
                'can close shift',
            ],
            'inventory-clerk' => [
                'can view pos item',
                'can create pos item',
                'can update pos item',
                'can view pos category',
                'can create pos category',
                'can update pos category',
            ],
        ];

        $backendRoles = [
            'backend-admin' => $backendPermissions,
            'backend-operator' => [
                'can access backend panel',
                ...$this->modelPermissions('pos item'),
                ...$this->modelPermissions('pos category'),
                'can view user',
                'can view backend user',
            ],
            'backend-analyst' => [
                'can access backend panel',
                'can view pos item',
                'can view pos category',
                'can view user',
                'can view backend user',
                'can view role',
                'can view dashboard analytics',
                'can export reports',
            ],
            'backend-support' => [
                'can access backend panel',
                'can view user',
                'can update user',
                'can view backend user',
                'can update backend user',
                'can view role',
            ],
        ];

        foreach ($webRoles as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions(array_values(array_unique($permissions)));
        }

        foreach ($backendRoles as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'backend',
            ]);

            $role->syncPermissions(array_values(array_unique($permissions)));
        }

        $clientAdmin = User::firstOrCreate(
            ['email' => 'client-admin@test.com'],
            [
                'name' => 'Super Admin Client',
                'password' => 'Hello123!',
                'email_verified_at' => now(),
            ]
        );

        $backendAdmin = BackendUser::firstOrCreate(
            ['email' => 'backend-admin@test.com'],
            [
                'name' => 'Super Admin',
                'password' => 'Hello123!',
                'email_verified_at' => now(),
            ]
        );

        $clientAdmin->assignRole('client-admin');
        $backendAdmin->assignRole('backend-admin');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        User::query()->where('email', 'client-admin@test.com')->delete();
        BackendUser::query()->where('email', 'backend-admin@test.com')->delete();
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

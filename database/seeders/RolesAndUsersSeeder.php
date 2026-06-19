<?php

namespace Database\Seeders;

use App\Models\BackendUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndUsersSeeder extends Seeder
{
    private const PASSWORD = 'Hello123!';

    public function run(): void
    {
        $activity = app(\Spatie\Activitylog\Support\ActivityLogStatus::class);

        $activity->disable();

        try {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $this->syncRoles('web', $this->webRoles());
            $this->syncRoles('backend', $this->backendRoles());
            $this->seedWebUsers();
            $this->seedBackendUsers();

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        } finally {
            $activity->enable();
        }
    }

    /**
     * @param  array<string, array<int, string>>  $roles
     */
    private function syncRoles(string $guard, array $roles): void
    {
        foreach (array_unique(array_merge(...array_values($roles))) as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
        }

        foreach ($roles as $roleName => $permissions) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard])
                ->syncPermissions(array_values(array_unique($permissions)));
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function webRoles(): array
    {
        $all = array_values(array_unique([
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

        $cashier = [
            'can view pos item',
            'can view pos category',
            'can process sale',
            'can apply discount',
            'can open shift',
            'can close shift',
        ];

        return [
            'client-admin' => $all,
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
            'cashier' => $cashier,
            'client-user' => $cashier,
            'inventory-clerk' => [
                'can view pos item',
                'can create pos item',
                'can update pos item',
                'can view pos category',
                'can create pos category',
                'can update pos category',
            ],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function backendRoles(): array
    {
        $operator = [
            'can access backend panel',
            ...$this->modelPermissions('pos item'),
            ...$this->modelPermissions('pos category'),
            'can view dashboard analytics',
        ];

        $analyst = [
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
        ];

        $support = [
            'can access backend panel',
            'can view user',
            'can view backend user',
            'can view dashboard analytics',
        ];

        return [
            'backend-admin' => array_values(array_unique([
                ...$operator,
                ...$analyst,
                ...$support,
                ...$this->modelPermissions('backend user'),
                ...$this->modelPermissions('user'),
                ...$this->modelPermissions('role'),
                'can export reports',
                'can export data',
                'can manage system settings',
                'can manage integrations',
            ])),
            'backend-operator' => $operator,
            'backend-analyst' => $analyst,
            'backend-support' => $support,
        ];
    }

    private function seedWebUsers(): void
    {
        $users = [
            'client-admin' => ['name' => 'Client Admin', 'email' => 'client-admin@test.com'],
            'store-manager' => ['name' => 'Store Manager', 'email' => 'store-manager@test.com'],
            'cashier' => ['name' => 'Cashier', 'email' => 'cashier@test.com'],
            'inventory-clerk' => ['name' => 'Inventory Clerk', 'email' => 'inventory-clerk@test.com'],
        ];

        foreach ($users as $role => $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => self::PASSWORD,
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$role]);
        }
    }

    private function seedBackendUsers(): void
    {
        $users = [
            'backend-admin' => ['name' => 'Backend Admin', 'email' => 'backend-admin@test.com'],
            'backend-operator' => ['name' => 'Backend Operator', 'email' => 'backend-operator@test.com'],
            'backend-analyst' => ['name' => 'Backend Analyst', 'email' => 'backend-analyst@test.com'],
            'backend-support' => ['name' => 'Backend Support', 'email' => 'backend-support@test.com'],
        ];

        foreach ($users as $role => $data) {
            $user = BackendUser::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => self::PASSWORD,
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$role]);
        }
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
}

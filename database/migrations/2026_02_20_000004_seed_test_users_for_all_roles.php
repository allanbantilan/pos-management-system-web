<?php

use App\Models\BackendUser;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
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

        $this->seedWebRoleUsers();
        $this->seedBackendRoleUsers();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $webEmails = [
            'client-admin@test.com',
            'store-manager@test.com',
            'cashier@test.com',
            'inventory-clerk@test.com',
        ];

        $backendEmails = [
            'backend-admin@test.com',
            'backend-operator@test.com',
            'backend-analyst@test.com',
            'backend-support@test.com',
        ];

        User::query()->whereIn('email', $webEmails)->delete();
        BackendUser::query()->whereIn('email', $backendEmails)->delete();
    }

    private function seedWebRoleUsers(): void
    {
        $webUsers = [
            'client-admin' => ['name' => 'Client Admin', 'email' => 'client-admin@test.com'],
            'store-manager' => ['name' => 'Store Manager', 'email' => 'store-manager@test.com'],
            'cashier' => ['name' => 'Cashier', 'email' => 'cashier@test.com'],
            'inventory-clerk' => ['name' => 'Inventory Clerk', 'email' => 'inventory-clerk@test.com'],
        ];

        foreach ($webUsers as $roleName => $data) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            if (! $role) {
                continue;
            }

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$role->name]);
        }
    }

    private function seedBackendRoleUsers(): void
    {
        $backendUsers = [
            'backend-admin' => ['name' => 'Backend Admin', 'email' => 'backend-admin@test.com'],
            'backend-operator' => ['name' => 'Backend Operator', 'email' => 'backend-operator@test.com'],
            'backend-analyst' => ['name' => 'Backend Analyst', 'email' => 'backend-analyst@test.com'],
            'backend-support' => ['name' => 'Backend Support', 'email' => 'backend-support@test.com'],
        ];

        foreach ($backendUsers as $roleName => $data) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'backend')
                ->first();

            if (! $role) {
                continue;
            }

            $user = BackendUser::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$role->name]);
        }
    }
};

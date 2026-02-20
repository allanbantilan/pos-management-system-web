<?php

namespace Database\Seeders;

use App\Models\BackendUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndUsersSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $clientRole = Role::firstOrCreate([
            'name' => 'client-user',
            'guard_name' => 'web',
        ]);

        $backendAdminRole = Role::firstOrCreate([
            'name' => 'backend-admin',
            'guard_name' => 'backend',
        ]);

        $clientUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => 'password']
        );
        $clientUser->assignRole($clientRole);

        $backendUser = BackendUser::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Backend Admin', 'password' => 'password']
        );
        $backendUser->assignRole($backendAdminRole);
    }
}

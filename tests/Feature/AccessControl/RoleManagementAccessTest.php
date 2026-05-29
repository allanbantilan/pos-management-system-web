<?php

namespace Tests\Feature\AccessControl;

use App\Filament\Resources\Roles\RoleResource;
use App\Models\BackendUser;
use App\Policies\RolePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementAccessTest extends TestCase
{
    use RefreshDatabase;

    private function backendUser(string $role): BackendUser
    {
        $user = BackendUser::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_super_admin_can_access_role_resource(): void
    {
        $this->actingAs($this->backendUser('backend-admin'), 'backend');

        $this->assertTrue(RoleResource::canAccess());
    }

    public function test_non_super_admin_cannot_access_role_resource(): void
    {
        $this->actingAs($this->backendUser('backend-operator'), 'backend');

        $this->assertFalse(RoleResource::canAccess());
    }

    public function test_protected_system_role_cannot_be_deleted(): void
    {
        $admin = $this->backendUser('backend-admin');
        $policy = new RolePolicy;

        $protected = Role::query()
            ->where('name', 'backend-admin')
            ->where('guard_name', 'backend')
            ->firstOrFail();

        $this->assertFalse($policy->delete($admin, $protected));
    }

    public function test_non_protected_role_can_be_deleted_by_super_admin(): void
    {
        $admin = $this->backendUser('backend-admin');
        $policy = new RolePolicy;

        $custom = Role::create(['name' => 'temp-role', 'guard_name' => 'backend']);

        $this->assertTrue($policy->delete($admin, $custom));
    }
}

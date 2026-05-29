<?php

namespace Tests\Feature\AccessControl;

use App\Models\AppSetting;
use App\Models\BackendUser;
use App\Models\Receipt;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PolicyEnforcementTest extends TestCase
{
    use RefreshDatabase;

    private function backendUser(string $role): BackendUser
    {
        $user = BackendUser::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    /**
     * AppSetting / Transaction / Receipt previously had no registered policy,
     * so any authenticated backend user could reach them. They must now be
     * gated behind the appropriate permission.
     */
    public function test_settings_and_reports_are_gated_by_permission(): void
    {
        $admin = $this->backendUser('backend-admin');     // has all backend permissions
        $operator = $this->backendUser('backend-operator'); // no settings / analytics perms

        foreach ([AppSetting::class, Transaction::class, Receipt::class] as $model) {
            $this->assertTrue(
                Gate::forUser($admin)->check('viewAny', $model),
                "Super admin should be able to view {$model}"
            );
            $this->assertFalse(
                Gate::forUser($operator)->check('viewAny', $model),
                "A non-privileged backend user must not view {$model}"
            );
        }
    }
}

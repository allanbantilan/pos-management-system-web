<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_throttled_after_five_failed_attempts(): void
    {
        $email = 'throttle@test.com';
        User::factory()->create(['email' => $email]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', [
                'email' => $email,
                'password' => 'wrong-password',
            ])->assertStatus(302); // invalid credentials, redirected back
        }

        // The 6th attempt within the window is blocked by throttle:login.
        $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }
}

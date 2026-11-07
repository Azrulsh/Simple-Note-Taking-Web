<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        RateLimiter::clear('login:ratelimituser|127.0.0.1');

        parent::tearDown();
    }

    public function test_user_is_blocked_after_five_failed_login_attempts(): void
    {
        User::factory()->create([
            'name' => 'ratelimituser',
            'password' => Hash::make('password1@3'),
        ]);

        // Five failed login attempts
        for ($i = 1; $i <= 5; $i++) {
            $this->post(route('manage-login.PostLogin'), [
                'name' => 'ratelimituser',
                'password' => 'wrongpassword',
            ])->assertSessionHas(
                'error',
                'Login credentials do not match our records'
            );
        }

        // Sixth attempt with correct password must be blocked
        $response = $this->post(route('manage-login.PostLogin'), [
            'name' => 'ratelimituser',
            'password' => 'password1@3',
        ]);

        $response->assertSessionHas('error', function ($message) {
            return str_contains($message, 'Too many login attempts');
        });

        $this->assertGuest();
    }

    public function test_user_can_login_after_rate_limit_expires(): void
    {
        User::factory()->create([
            'name' => 'ratelimituser',
            'password' => Hash::make('password1@3'),
        ]);

        for ($i = 1; $i <= 5; $i++) {
            $this->post(route('manage-login.PostLogin'), [
                'name' => 'ratelimituser',
                'password' => 'wrongpassword',
            ]);
        }

        // Simulate 61 seconds passing
        $this->travel(61)->seconds();

        $response = $this->post(route('manage-login.PostLogin'), [
            'name' => 'ratelimituser',
            'password' => 'password1@3',
        ]);

        $response->assertRedirect(route('manage-note.IndexNote'));

        $this->assertAuthenticated();
    }

    public function test_successful_login_clears_failed_attempts(): void
    {
        $user = User::factory()->create([
            'name' => 'ratelimituser',
            'password' => Hash::make('password1@3'),
        ]);

        // Simulate 3 failed login attempts
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('manage-login.PostLogin'), [
                'name' => 'ratelimituser',
                'password' => 'wrongpassword',
            ]);
        }

        $key = 'login:ratelimituser|127.0.0.1';

        // Verify failed attempts have been recorded
        $this->assertSame(3, RateLimiter::attempts($key));

        // Successful login
        $response = $this->post(route('manage-login.PostLogin'), [
            'name' => 'ratelimituser',
            'password' => 'password1@3',
        ]);

        $response->assertRedirect(route('manage-note.IndexNote'));

        $this->assertAuthenticatedAs($user);

        // Verify failed attempts are cleared
        $this->assertSame(0, RateLimiter::attempts($key));
    }
}
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

         /** @var \App\Models\User $user */
        $response = $this->actingAs($user)
            ->post(route('manage-login.Logout'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $this->assertGuest();
    }

    public function test_logout_does_not_accept_get_request(): void
    {
        $user = User::factory()->create();

         /** @var \App\Models\User $user */
        $response = $this->actingAs($user)
            ->get(route('manage-login.Logout'));

        $response->assertStatus(405);

        $this->assertAuthenticatedAs($user);
    }

    public function test_guest_cannot_access_logout(): void
    {
        $response = $this->post(route('manage-login.Logout'));

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
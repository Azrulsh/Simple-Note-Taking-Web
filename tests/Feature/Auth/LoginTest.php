<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'name' => 'testuser',
            'password' => Hash::make('password1@3'),
        ]);

        $response = $this->post(route('manage-login.PostLogin'), [
            'name' => 'testuser',
            'password' => 'password1@3',
        ]);

        $response->assertRedirect(route('manage-note.IndexNote'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'name' => 'testuser',
            'password' => Hash::make('password1@3'),
        ]);

        $response = $this->post(route('manage-login.PostLogin'), [
            'name' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();

        $response->assertSessionHas('error');
    }

    public function test_user_cannot_login_with_nonexistent_username(): void
    {
        $response = $this->post(route('manage-login.PostLogin'), [
            'name' => 'unknownuser',
            'password' => 'password1@3',
        ]);

        $this->assertGuest();

        $response->assertSessionHas('error');
    }

    public function test_login_requires_username(): void
    {
        $response = $this->post(route('manage-login.PostLogin'), [
            'name' => '',
            'password' => 'password1@3',
        ]);

        $response->assertSessionHasErrors('name');

        $this->assertGuest();
    }

    public function test_login_requires_password(): void
    {
        $response = $this->post(route('manage-login.PostLogin'), [
            'name' => 'testuser',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertGuest();
    }
}
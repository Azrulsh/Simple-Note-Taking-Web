<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_successfully(): void
    {
        $response = $this->post(route('manage-login.PostRegistration'), [
            'name' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => 'password1@3',
            'password_confirmation' => 'password1@3',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('login_record', [
            'name' => 'testuser',
            'email' => 'testuser@example.com',
        ]);

        $this->assertTrue(
            User::where('name', 'testuser')->exists()
        );
    }

    public function test_registration_fails_with_duplicate_username(): void
    {
        User::factory()->create([
            'name' => 'testuser',
            'email' => 'existing@example.com',
        ]);

        $response = $this->post(route('manage-login.PostRegistration'), [
            'name' => 'testuser',
            'email' => 'new@example.com',
            'password' => 'password1@3',
            'password_confirmation' => 'password1@3',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create([
            'name' => 'existinguser',
            'email' => 'testuser@example.com',
        ]);

        $response = $this->post(route('manage-login.PostRegistration'), [
            'name' => 'newuser',
            'email' => 'testuser@example.com',
            'password' => 'password1@3',
            'password_confirmation' => 'password1@3',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_registration_fails_with_short_password(): void
    {
        $response = $this->post(route('manage-login.PostRegistration'), [
            'name' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_registration_fails_with_mismatched_password_confirmation(): void
    {
        $response = $this->post(route('manage-login.PostRegistration'), [
            'name' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => 'password1@3',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
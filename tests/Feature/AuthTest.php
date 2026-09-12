<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that guest can view the login page.
     */
    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertViewIs('auth.login');
    }

    /**
     * Test that guest can view the registration page.
     */
    public function test_guest_can_view_register_page(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertViewIs('auth.register');
    }

    /**
     * Test that a new user can register and is authenticated.
     */
    public function test_user_can_register_and_is_authenticated(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Jordan Wolfe',
            'email' => 'jordan@apex.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Jordan Wolfe',
            'email' => 'jordan@apex.com',
        ]);
        $response->assertRedirect(route('team.create'));
    }

    /**
     * Test that registration validates required and unique fields.
     */
    public function test_registration_requires_valid_data(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post(route('register'), [
            'name' => '',
            'email' => 'taken@example.com',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    /**
     * Test that an existing user can log in with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'manager@speed.com',
            'password' => 'secret123',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'manager@speed.com',
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('team.create'));
    }

    /**
     * Test that login fails with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'manager@speed.com',
            'password' => 'secret123',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'manager@speed.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test that an authenticated user can log out.
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    /**
     * Test that unauthenticated users are redirected to login from protected routes.
     */
    public function test_unauthenticated_user_is_redirected_to_login_from_protected_routes(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }
}

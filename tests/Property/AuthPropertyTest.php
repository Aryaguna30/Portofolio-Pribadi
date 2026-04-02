<?php

// Feature: portfolio-website, Property 15, 16, 17

namespace Tests\Property;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Property 15: Invalid Login Credentials Rejected
 * Property 16: Admin Routes Protected by Auth Guard
 * Property 17: Brute Force Protection on Login
 *
 * Validates: Requirements 8.3, 8.4, 8.6
 */
class AuthPropertyTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Property 15: Invalid Login Credentials Rejected
    // -------------------------------------------------------------------------

    public function test_wrong_password_always_fails_authentication(): void
    {
        User::factory()->create([
            'email'    => 'admin@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $wrongPasswords = [
            'wrong-password',
            'CORRECT-PASSWORD',
            'password',
            str_repeat('x', 50),
        ];

        foreach ($wrongPasswords as $i => $wrongPassword) {
            $response = $this->post('/admin/login', [
                'email'    => 'admin@example.com',
                'password' => $wrongPassword,
            ]);

            $this->assertGuest(null, "Iteration {$i}: wrong password must not authenticate");
            $response->assertSessionHasErrors('email');
        }
    }

    public function test_nonexistent_email_always_fails_authentication(): void
    {
        $emails = [
            'nobody@example.com',
            'admin@example.com',
            'a@b.com',
        ];

        foreach ($emails as $i => $email) {
            $response = $this->post('/admin/login', [
                'email'    => $email,
                'password' => 'any-password',
            ]);

            $this->assertGuest(null, "Iteration {$i}: non-existent email must not authenticate");
            $response->assertSessionHasErrors('email');
        }
    }

    public function test_valid_credentials_authenticate_successfully(): void
    {
        $user = User::factory()->create([
            'email'    => 'admin@example.com',
            'password' => Hash::make('secret-password'),
        ]);

        $this->post('/admin/login', [
            'email'    => 'admin@example.com',
            'password' => 'secret-password',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    // -------------------------------------------------------------------------
    // Property 16: Admin Routes Protected by Auth Guard
    // -------------------------------------------------------------------------

    public function test_admin_routes_redirect_unauthenticated_users(): void
    {
        $adminRoutes = [
            '/admin/dashboard',
            '/admin/landing',
            '/admin/projects',
            '/admin/messages',
        ];

        foreach ($adminRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/admin/login');
        }
    }

    public function test_post_to_admin_routes_redirects_unauthenticated(): void
    {
        $response = $this->put('/admin/landing/hero', ['hero_name' => 'Test']);
        $response->assertRedirect('/admin/login');
    }

    // -------------------------------------------------------------------------
    // Property 17: Brute Force Protection on Login
    // -------------------------------------------------------------------------

    public function test_brute_force_protection_blocks_after_five_attempts(): void
    {
        RateLimiter::clear('login|127.0.0.1');

        User::factory()->create([
            'email'    => 'admin@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        for ($i = 1; $i <= 5; $i++) {
            $this->post('/admin/login', [
                'email'    => 'admin@example.com',
                'password' => 'wrong-password-' . $i,
            ]);
        }

        $response = $this->post('/admin/login', [
            'email'    => 'admin@example.com',
            'password' => 'wrong-password-6',
        ]);

        $response->assertStatus(429);
    }

    public function test_first_login_attempt_is_never_rate_limited(): void
    {
        RateLimiter::clear('login|127.0.0.1');

        $response = $this->post('/admin/login', [
            'email'    => 'nobody@example.com',
            'password' => 'any-password',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }
}

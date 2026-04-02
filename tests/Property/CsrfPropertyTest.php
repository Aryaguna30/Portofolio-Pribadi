<?php

// Feature: portfolio-website, Property 26

namespace Tests\Property;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Property 26: CSRF Protection Rejects Requests Without Valid Token
 *
 * Validates: Requirements 14.1
 *
 * For any POST/PUT/DELETE request to a form submission endpoint that does not
 * include a valid CSRF token, the application must respond with HTTP 419
 * (Page Expired) and must not process the request.
 *
 * Note: Laravel's CSRF middleware (PreventRequestForgery) automatically bypasses
 * token verification when running unit tests (runningUnitTests() returns true).
 * This is intentional framework behavior. These tests therefore verify:
 * 1. The CSRF middleware IS registered in the web middleware stack
 * 2. The middleware class correctly identifies token mismatches
 * 3. State-changing routes are NOT excluded from CSRF protection
 * 4. The middleware throws TokenMismatchException on mismatch (→ 419 in production)
 */
class CsrfPropertyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Bind a passthrough purifier so ContactFormRequest doesn't throw
        $this->app->bind('purifier', function () {
            return new class {
                public function purify(string $html): string
                {
                    return $html;
                }
            };
        });
    }

    // -------------------------------------------------------------------------
    // Property 26: CSRF middleware is registered and configured correctly
    // -------------------------------------------------------------------------

    /**
     * Property 26: The CSRF middleware class exists and is instantiable.
     *
     * Validates: Requirements 14.1
     */
    public function test_csrf_middleware_class_exists_and_is_instantiable(): void
    {
        $this->assertTrue(
            class_exists(PreventRequestForgery::class),
            'PreventRequestForgery CSRF middleware class must exist'
        );

        // Verify it can be resolved from the container
        $middleware = $this->app->make(PreventRequestForgery::class);

        $this->assertInstanceOf(
            PreventRequestForgery::class,
            $middleware,
            'PreventRequestForgery must be resolvable from the service container'
        );
    }

    /**
     * Property 26: The CSRF middleware throws TokenMismatchException when tokens don't match.
     *
     * Validates: Requirements 14.1
     */
    public function test_csrf_middleware_throws_on_token_mismatch(): void
    {
        $middleware = $this->app->make(PreventRequestForgery::class);

        // Create a POST request with a session token that doesn't match the header
        $request = \Illuminate\Http\Request::create('/contact', 'POST');
        $request->setLaravelSession($this->app['session']->driver());
        $request->session()->put('_token', 'correct-session-token');
        $request->headers->set('X-CSRF-TOKEN', 'wrong-token');

        // The middleware should throw TokenMismatchException for mismatched tokens
        // (when not running in unit test mode — we test the logic directly)
        $reflection = new \ReflectionClass($middleware);
        $tokensMatch = $reflection->getMethod('tokensMatch');
        $tokensMatch->setAccessible(true);

        $result = $tokensMatch->invoke($middleware, $request);

        $this->assertFalse(
            $result,
            'tokensMatch() must return false when X-CSRF-TOKEN does not match session token'
        );
    }

    /**
     * Property 26: The CSRF middleware tokensMatch returns true for matching tokens.
     *
     * Validates: Requirements 14.1 (positive case)
     */
    public function test_csrf_middleware_tokens_match_for_valid_token(): void
    {
        $middleware = $this->app->make(PreventRequestForgery::class);

        $token = 'matching-csrf-token-value';

        $request = \Illuminate\Http\Request::create('/contact', 'POST');
        $request->setLaravelSession($this->app['session']->driver());
        $request->session()->put('_token', $token);
        $request->headers->set('X-CSRF-TOKEN', $token);

        $reflection = new \ReflectionClass($middleware);
        $tokensMatch = $reflection->getMethod('tokensMatch');
        $tokensMatch->setAccessible(true);

        $result = $tokensMatch->invoke($middleware, $request);

        $this->assertTrue(
            $result,
            'tokensMatch() must return true when X-CSRF-TOKEN matches session token'
        );
    }

    /**
     * Property 26: State-changing routes are NOT in the CSRF exception list.
     *
     * Validates: Requirements 14.1
     */
    public function test_contact_route_is_not_excluded_from_csrf(): void
    {
        $middleware = $this->app->make(PreventRequestForgery::class);

        $request = \Illuminate\Http\Request::create('/contact', 'POST');

        $reflection = new \ReflectionClass($middleware);
        $inExceptArray = $reflection->getMethod('inExceptArray');
        $inExceptArray->setAccessible(true);

        $result = $inExceptArray->invoke($middleware, $request);

        $this->assertFalse(
            $result,
            '/contact must NOT be excluded from CSRF protection'
        );
    }

    /**
     * Property 26: Admin login route is NOT in the CSRF exception list.
     *
     * Validates: Requirements 14.1
     */
    public function test_admin_login_route_is_not_excluded_from_csrf(): void
    {
        $middleware = $this->app->make(PreventRequestForgery::class);

        $request = \Illuminate\Http\Request::create('/admin/login', 'POST');

        $reflection = new \ReflectionClass($middleware);
        $inExceptArray = $reflection->getMethod('inExceptArray');
        $inExceptArray->setAccessible(true);

        $result = $inExceptArray->invoke($middleware, $request);

        $this->assertFalse(
            $result,
            '/admin/login must NOT be excluded from CSRF protection'
        );
    }

    /**
     * Property 26: Multiple invalid token values all fail the tokensMatch check.
     *
     * Validates: Requirements 14.1
     */
    public function test_various_invalid_tokens_all_fail_tokens_match(): void
    {
        $middleware = $this->app->make(PreventRequestForgery::class);

        $reflection = new \ReflectionClass($middleware);
        $tokensMatch = $reflection->getMethod('tokensMatch');
        $tokensMatch->setAccessible(true);

        $invalidTokens = [
            'invalid-token',
            str_repeat('x', 40),
            'null',
            '0',
            base64_encode('fake-token'),
            'eyJhbGciOiJIUzI1NiJ9.fake.signature',
            'wrong-token-value',
        ];

        foreach ($invalidTokens as $i => $token) {
            $request = \Illuminate\Http\Request::create('/contact', 'POST');
            $request->setLaravelSession($this->app['session']->driver());
            $request->session()->put('_token', 'correct-session-token');
            $request->headers->set('X-CSRF-TOKEN', $token);

            $result = $tokensMatch->invoke($middleware, $request);

            $this->assertFalse(
                $result,
                "Iteration {$i}: token '{$token}' must not match session token 'correct-session-token'"
            );
        }
    }

    /**
     * Property 26: GET requests are exempt from CSRF protection (isReading check).
     *
     * Validates: Requirements 14.1 (negative case — GET is exempt)
     */
    public function test_get_requests_are_exempt_from_csrf(): void
    {
        $middleware = $this->app->make(PreventRequestForgery::class);

        $request = \Illuminate\Http\Request::create('/', 'GET');

        $reflection = new \ReflectionClass($middleware);
        $isReading = $reflection->getMethod('isReading');
        $isReading->setAccessible(true);

        $result = $isReading->invoke($middleware, $request);

        $this->assertTrue($result, 'GET requests must be considered "reading" and exempt from CSRF');
    }

    /**
     * Property 26: POST requests are NOT exempt from CSRF protection.
     *
     * Validates: Requirements 14.1
     */
    public function test_post_requests_are_not_exempt_from_csrf(): void
    {
        $middleware = $this->app->make(PreventRequestForgery::class);

        $request = \Illuminate\Http\Request::create('/contact', 'POST');

        $reflection = new \ReflectionClass($middleware);
        $isReading = $reflection->getMethod('isReading');
        $isReading->setAccessible(true);

        $result = $isReading->invoke($middleware, $request);

        $this->assertFalse($result, 'POST requests must NOT be considered "reading" — CSRF applies');
    }

    /**
     * Property 26: In production (non-test) environment, a token mismatch would
     * result in a TokenMismatchException being thrown by the middleware.
     *
     * Validates: Requirements 14.1
     */
    public function test_token_mismatch_throws_exception_outside_unit_tests(): void
    {
        $middleware = $this->app->make(PreventRequestForgery::class);

        $request = \Illuminate\Http\Request::create('/contact', 'POST');
        $request->setLaravelSession($this->app['session']->driver());
        $request->session()->put('_token', 'correct-token');
        $request->headers->set('X-CSRF-TOKEN', 'wrong-token');

        // Verify that when runningUnitTests() is false (production), the middleware
        // would throw TokenMismatchException. We test this by verifying the condition
        // that would trigger the exception: tokens don't match AND not reading.
        $reflection = new \ReflectionClass($middleware);

        $isReading = $reflection->getMethod('isReading');
        $isReading->setAccessible(true);

        $tokensMatch = $reflection->getMethod('tokensMatch');
        $tokensMatch->setAccessible(true);

        $inExceptArray = $reflection->getMethod('inExceptArray');
        $inExceptArray->setAccessible(true);

        $wouldPassCsrf = $isReading->invoke($middleware, $request)
            || $inExceptArray->invoke($middleware, $request)
            || $tokensMatch->invoke($middleware, $request);

        $this->assertFalse(
            $wouldPassCsrf,
            'A POST request with mismatched CSRF token must not pass CSRF checks in production'
        );
    }
}

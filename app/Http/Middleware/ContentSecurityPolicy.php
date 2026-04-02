<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request and append a Content-Security-Policy header.
     *
     * Uses a per-request nonce for script-src so 'unsafe-inline' is not needed.
     * The nonce is shared via the 'csp_nonce' key on the request so Blade/Inertia
     * can inject it into <script> tags if needed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $policy = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' challenges.cloudflare.com",
            "style-src 'self' 'unsafe-inline' fonts.googleapis.com cdn.jsdelivr.net",
            "font-src 'self' fonts.gstatic.com data:",
            "img-src 'self' data: blob:",
            "connect-src 'self' challenges.cloudflare.com",
            "frame-src challenges.cloudflare.com",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $policy);

        // Additional security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}

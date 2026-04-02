<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request and append a Content-Security-Policy header.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $policy = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' fonts.googleapis.com cdn.jsdelivr.net",
            "font-src 'self' fonts.gstatic.com data:",
            "img-src 'self' data: blob:",
            "connect-src 'self'",
            "frame-ancestors 'none'",
        ]);

        $response->headers->set('Content-Security-Policy', $policy);

        return $response;
    }
}

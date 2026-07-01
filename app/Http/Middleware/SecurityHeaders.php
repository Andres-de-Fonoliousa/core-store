<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        if (!app()->environment('local')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $response->headers->remove('X-Frame-Options');
        $response->headers->set('Content-Security-Policy', $this->cspPolicy());

        return $response;
    }

    private function cspPolicy(): string
    {
        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com https://unpkg.com https://cdn.tailwindcss.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "img-src 'self' data: blob: https:",
            "font-src 'self' https://fonts.gstatic.com",
            "connect-src 'self'",
            "frame-src 'self' https://js.stripe.com",
            "frame-ancestors *",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        return implode('; ', $directives);
    }
}

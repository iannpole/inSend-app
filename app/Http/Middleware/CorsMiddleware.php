<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /** Origins allowed to call this API (set CORS_ALLOWED_ORIGINS in .env, comma-separated). */
    private function getAllowedOrigins(): array
    {
        $raw = env('CORS_ALLOWED_ORIGINS', env('APP_URL', 'http://localhost'));
        return array_map('trim', explode(',', $raw));
    }

    private function resolveOrigin(Request $request): string
    {
        $origin  = $request->headers->get('Origin', '');
        $allowed = $this->getAllowedOrigins();

        // Mobile apps (Flutter) send no Origin header — always allowed
        if (empty($origin)) {
            return '*';
        }

        return in_array($origin, $allowed, true) ? $origin : '';
    }

    public function handle(Request $request, Closure $next): Response
    {
        $allowedOrigin = $this->resolveOrigin($request);

        // Reject cross-origin requests from unknown domains
        if ($allowedOrigin === '' && $request->isMethod('OPTIONS')) {
            return response('Forbidden', 403);
        }

        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', $allowedOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        $response = $next($request);

        if ($allowedOrigin !== '') {
            $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        // Security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}

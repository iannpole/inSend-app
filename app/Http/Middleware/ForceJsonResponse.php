<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        // Memaksa request untuk selalu menggunakan header JSON
        $request->headers->set('Accept', 'application/json');
        
        // Opsional: Memastikan response yang keluar juga berformat JSON
        $response = $next($request);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
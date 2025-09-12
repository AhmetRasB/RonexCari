<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HostingOptimization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set memory limit for shared hosting
        if (config('hosting.shared_hosting_optimizations.memory_limit_override')) {
            ini_set('memory_limit', config('hosting.shared_hosting_optimizations.memory_limit_override'));
        }

        // Set execution time limit
        if (config('hosting.shared_hosting_optimizations.max_execution_time_override')) {
            set_time_limit(config('hosting.shared_hosting_optimizations.max_execution_time_override'));
        }

        // Disable SSL verification if needed
        if (config('hosting.shared_hosting_optimizations.disable_ssl_verification')) {
            // This will be handled in the HTTP client configuration
        }

        // Add security headers for shared hosting
        $response = $next($request);

        // Add security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Add cache headers for static assets
        if ($request->is('assets/*') || $request->is('css/*') || $request->is('js/*')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000');
        }

        return $response;
    }
}
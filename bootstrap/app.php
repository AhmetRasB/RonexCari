<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'account.selection' => \App\Http\Middleware\AccountSelectionMiddleware::class,
        ]);
        
        // Add account selection middleware to web group
        $middleware->web(append: [
            \App\Http\Middleware\AccountSelectionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Gracefully handle CSRF token mismatch (419 Page Expired)
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            // Try to regenerate a fresh CSRF token and redirect back
            try {
                if ($request->hasSession()) {
                    $request->session()->regenerateToken();
                }
            } catch (\Throwable $t) {
                // ignore
            }
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Oturum yenilendi, lütfen işlemi tekrar deneyin.'], 419);
            }
            return redirect()->back()->with('status', 'Oturum yenilendi, lütfen işlemi tekrar deneyin.');
        });
    })->create();

<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class HostingExceptionHandler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log currency API errors specifically
            if (str_contains($e->getMessage(), 'Currency') || 
                str_contains($e->getMessage(), 'Trunçgil') ||
                str_contains($e->getMessage(), 'exchange')) {
                Log::error('Currency API Error: ' . $e->getMessage(), [
                    'exception' => $e,
                    'url' => request()->url(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Handle currency API errors gracefully
        if (str_contains($e->getMessage(), 'Currency') || 
            str_contains($e->getMessage(), 'Trunçgil') ||
            str_contains($e->getMessage(), 'exchange')) {
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Döviz kuru servisi geçici olarak kullanılamıyor. Lütfen manuel olarak girin.',
                    'error' => 'CURRENCY_API_ERROR',
                    'fallback' => true
                ], 503);
            }

            // For web requests, redirect back with error message
            return redirect()->back()->with('error', 
                'Döviz kuru servisi geçici olarak kullanılamıyor. Lütfen manuel olarak girin.'
            );
        }

        // Handle cURL errors
        if (str_contains($e->getMessage(), 'cURL') || 
            str_contains($e->getMessage(), 'SSL') ||
            str_contains($e->getMessage(), 'certificate')) {
            
            Log::warning('cURL/SSL Error in hosting environment', [
                'error' => $e->getMessage(),
                'url' => $request->url(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bağlantı hatası. Lütfen daha sonra tekrar deneyin.',
                    'error' => 'CONNECTION_ERROR'
                ], 503);
            }

            return redirect()->back()->with('error', 
                'Bağlantı hatası. Lütfen daha sonra tekrar deneyin.'
            );
        }

        // Handle memory limit errors
        if (str_contains($e->getMessage(), 'memory') || 
            str_contains($e->getMessage(), 'Memory')) {
            
            Log::error('Memory limit exceeded', [
                'error' => $e->getMessage(),
                'memory_limit' => ini_get('memory_limit'),
                'url' => $request->url(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sistem geçici olarak yoğun. Lütfen daha sonra tekrar deneyin.',
                    'error' => 'MEMORY_LIMIT_ERROR'
                ], 503);
            }

            return redirect()->back()->with('error', 
                'Sistem geçici olarak yoğun. Lütfen daha sonra tekrar deneyin.'
            );
        }

        // Handle database connection errors
        if (str_contains($e->getMessage(), 'database') || 
            str_contains($e->getMessage(), 'SQL') ||
            str_contains($e->getMessage(), 'Connection')) {
            
            Log::error('Database connection error', [
                'error' => $e->getMessage(),
                'url' => $request->url(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Veritabanı bağlantı hatası. Lütfen daha sonra tekrar deneyin.',
                    'error' => 'DATABASE_ERROR'
                ], 503);
            }

            return redirect()->back()->with('error', 
                'Veritabanı bağlantı hatası. Lütfen daha sonra tekrar deneyin.'
            );
        }

        return parent::render($request, $e);
    }
}

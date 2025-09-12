<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class HostingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register hosting-specific configurations
        $this->app->singleton('hosting.currency', function ($app) {
            return new \App\Services\CurrencyService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Configure HTTP client for shared hosting
        $this->configureHttpClient();
        
        // Set PHP settings for shared hosting
        $this->configurePhpSettings();
        
        // Register hosting-specific error handling
        $this->registerErrorHandling();
    }

    /**
     * Configure HTTP client for shared hosting environments
     */
    private function configureHttpClient(): void
    {
        Http::macro('hosting', function () {
            return Http::timeout(config('hosting.http_client.timeout', 30))
                ->connectTimeout(config('hosting.http_client.connect_timeout', 10))
                ->withOptions([
                    'verify' => config('hosting.http_client.verify_ssl', false),
                    'http_errors' => false,
                    'headers' => [
                        'User-Agent' => config('hosting.http_client.user_agent', 'Laravel Currency Service/1.0'),
                        'Accept' => 'application/json',
                    ]
                ]);
        });
    }

    /**
     * Configure PHP settings for shared hosting
     */
    private function configurePhpSettings(): void
    {
        // Only apply these settings in production
        if (app()->environment('production')) {
            $memoryLimit = config('hosting.shared_hosting_optimizations.memory_limit_override');
            if ($memoryLimit) {
                ini_set('memory_limit', $memoryLimit);
            }

            $maxExecutionTime = config('hosting.shared_hosting_optimizations.max_execution_time_override');
            if ($maxExecutionTime) {
                set_time_limit($maxExecutionTime);
            }
        }
    }

    /**
     * Register hosting-specific error handling
     */
    private function registerErrorHandling(): void
    {
        // Set custom error handler for currency API errors
        set_error_handler(function ($severity, $message, $file, $line) {
            if (str_contains($message, 'Currency') || 
                str_contains($message, 'Trunçgil') ||
                str_contains($message, 'exchange')) {
                
                \Log::warning('Currency API Error in hosting environment', [
                    'message' => $message,
                    'file' => $file,
                    'line' => $line,
                    'severity' => $severity
                ]);
                
                // Don't execute PHP internal error handler
                return true;
            }
            
            // Let PHP handle other errors
            return false;
        });
    }
}
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default timezone for Carbon (Istanbul/Turkey GMT+3)
        Carbon::setLocale('tr'); // Turkish locale
        date_default_timezone_set('Europe/Istanbul');

        // Force HTTPS URLs in production to prevent mixed content
        if (app()->environment('production')) {
            $appUrl = config('app.url');
            $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'http';
            if ($scheme === 'https') {
                URL::forceRootUrl($appUrl);
                URL::forceScheme('https');
            }
        }
    }
}

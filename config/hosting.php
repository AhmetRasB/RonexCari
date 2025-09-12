<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Hosting Environment Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration settings optimized for shared hosting
    | environments where certain PHP functions might be restricted.
    |
    */

    'http_client' => [
        'timeout' => env('HTTP_TIMEOUT', 30),
        'connect_timeout' => env('HTTP_CONNECT_TIMEOUT', 10),
        'verify_ssl' => env('HTTP_VERIFY_SSL', false),
        'user_agent' => env('HTTP_USER_AGENT', 'Laravel Currency Service/1.0'),
    ],

    'currency_api' => [
        'fallback_methods' => [
            'laravel_http',  // Laravel HTTP Client
            'curl',          // cURL extension
            'file_get_contents', // file_get_contents
        ],
        'retry_attempts' => env('CURRENCY_API_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('CURRENCY_API_RETRY_DELAY', 2), // seconds
    ],

    'cache' => [
        'currency_rates_ttl' => env('CURRENCY_CACHE_TTL', 300), // 5 minutes
        'driver' => env('CACHE_DRIVER', 'file'),
    ],

    'logging' => [
        'currency_api_logs' => env('LOG_CURRENCY_API', true),
        'log_level' => env('LOG_LEVEL', 'info'),
    ],

    'fallback_rates' => [
        'USD' => env('FALLBACK_USD_RATE', 41.29),
        'EUR' => env('FALLBACK_EUR_RATE', 48.55),
    ],

    'shared_hosting_optimizations' => [
        'disable_ssl_verification' => true,
        'use_simple_http' => false, // Set to true if having issues
        'memory_limit_override' => '256M',
        'max_execution_time_override' => 60,
    ],
];

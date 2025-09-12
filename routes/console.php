<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Clear currency cache every 15 minutes
Schedule::call(function () {
    app(\App\Services\CurrencyService::class)->clearCache();
})->everyFifteenMinutes();

// Accumulate employee salaries daily
Schedule::command('employees:accumulate-salaries')->daily();

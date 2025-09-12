<?php

namespace App\Console\Commands;

use App\Services\CurrencyService;
use Illuminate\Console\Command;

class ClearCurrencyCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear currency exchange rates cache';

    /**
     * Execute the console command.
     */
    public function handle(CurrencyService $currencyService)
    {
        $currencyService->clearCache();
        
        $this->info('Currency exchange rates cache cleared successfully!');
        
        return Command::SUCCESS;
    }
}
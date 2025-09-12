<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Collection;

class FixCustomerBalances extends Command
{
    protected $signature = 'fix:customer-balances';
    protected $description = 'Fix customer balances based on invoices and collections';

    public function handle()
    {
        $customers = Customer::all();
        
        foreach ($customers as $customer) {
            // TRY Balance
            $unpaidInvoicesTRY = Invoice::where('customer_id', $customer->id)
                ->where('payment_completed', false)
                ->where('currency', 'TRY')
                ->sum('total_amount');
            
            $collectionsTRY = Collection::where('customer_id', $customer->id)
                ->where('currency', 'TRY')
                ->sum('amount');
            
            $newBalanceTRY = $unpaidInvoicesTRY - $collectionsTRY;
            $customer->balance_try = $newBalanceTRY;
            
            // USD Balance
            $unpaidInvoicesUSD = Invoice::where('customer_id', $customer->id)
                ->where('payment_completed', false)
                ->where('currency', 'USD')
                ->sum('total_amount');
            
            $collectionsUSD = Collection::where('customer_id', $customer->id)
                ->where('currency', 'USD')
                ->sum('amount');
            
            $newBalanceUSD = $unpaidInvoicesUSD - $collectionsUSD;
            $customer->balance_usd = $newBalanceUSD;
            
            // EUR Balance
            $unpaidInvoicesEUR = Invoice::where('customer_id', $customer->id)
                ->where('payment_completed', false)
                ->where('currency', 'EUR')
                ->sum('total_amount');
            
            $collectionsEUR = Collection::where('customer_id', $customer->id)
                ->where('currency', 'EUR')
                ->sum('amount');
            
            $newBalanceEUR = $unpaidInvoicesEUR - $collectionsEUR;
            $customer->balance_eur = $newBalanceEUR;
            
            $customer->save();
            
            $this->info("Customer {$customer->name}: TRY={$newBalanceTRY}, USD={$newBalanceUSD}, EUR={$newBalanceEUR}");
        }
        
        $this->info('Customer balances fixed successfully!');
    }
}

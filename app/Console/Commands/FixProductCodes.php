<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixProductCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-product-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Fix null values
        $products = \DB::table('products')->whereNull('product_code')->get();
        foreach ($products as $product) {
            $productCode = 'PRD-' . str_pad($product->id, 6, '0', STR_PAD_LEFT);
            \DB::table('products')
                ->where('id', $product->id)
                ->update(['product_code' => $productCode]);
            $this->info("Updated product {$product->id} with code: {$productCode}");
        }
        
        // Fix empty string values
        $products = \DB::table('products')->where('product_code', '')->get();
        foreach ($products as $product) {
            $productCode = 'PRD-' . str_pad($product->id, 6, '0', STR_PAD_LEFT);
            \DB::table('products')
                ->where('id', $product->id)
                ->update(['product_code' => $productCode]);
            $this->info("Updated product {$product->id} with code: {$productCode}");
        }
        
        $this->info('All product codes have been updated!');
    }
}

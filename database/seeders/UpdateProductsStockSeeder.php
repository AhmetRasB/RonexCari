<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateProductsStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing products with initial_stock and critical_stock values
        \DB::table('products')->whereNull('initial_stock')->update([
            'initial_stock' => \DB::raw('stock_quantity'),
            'critical_stock' => 5
        ]);
        
        $this->command->info('Products updated with initial_stock and critical_stock values!');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductSeries;
use App\Models\Account;

class UpdateProductsAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // RONEX1 hesabını bul (Gömlek kategorisi)
        $ronex1 = Account::where('code', 'RONEX1')->first();
        
        // RONEX2 hesabını bul (Ceket, Takım Elbise, Pantalon kategorileri)
        $ronex2 = Account::where('code', 'RONEX2')->first();
        
        if ($ronex1) {
            // Gömlek kategorisindeki ürünleri RONEX1 hesabına ata
            Product::where('category', 'Gömlek')->update(['account_id' => $ronex1->id]);
            ProductSeries::where('category', 'Gömlek')->update(['account_id' => $ronex1->id]);
        }
        
        if ($ronex2) {
            // Ceket, Takım Elbise, Pantalon kategorisindeki ürünleri RONEX2 hesabına ata
            Product::whereIn('category', ['Ceket', 'Takım Elbise', 'Pantalon'])->update(['account_id' => $ronex2->id]);
            ProductSeries::whereIn('category', ['Ceket', 'Takım Elbise', 'Pantalon'])->update(['account_id' => $ronex2->id]);
        }
        
        $this->command->info('Products and ProductSeries have been updated with account assignments.');
    }
}
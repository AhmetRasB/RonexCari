<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Collection;
use App\Models\Customer;
use Carbon\Carbon;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::where('is_active', true)->get();
        
        if ($customers->isEmpty()) {
            $this->command->warn('No active customers found. Please run CustomerSeeder first.');
            return;
        }

        $collectionTypes = ['nakit', 'banka', 'kredi_karti', 'havale', 'eft'];
        $currencies = ['TRY', 'USD', 'EUR'];
        
        // Son 3 ay içinde rastgele tahsilatlar oluştur
        for ($i = 0; $i < 50; $i++) {
            $customer = $customers->random();
            $collectionType = $collectionTypes[array_rand($collectionTypes)];
            $currency = $currencies[array_rand($currencies)];
            
            // Para birimine göre tutar aralığı
            $amount = match($currency) {
                'TRY' => rand(100, 50000) + (rand(0, 99) / 100),
                'USD' => rand(50, 2000) + (rand(0, 99) / 100),
                'EUR' => rand(50, 2000) + (rand(0, 99) / 100),
                default => rand(100, 5000) + (rand(0, 99) / 100)
            };
            
            // Son 90 gün içinde rastgele tarih
            $transactionDate = Carbon::now()->subDays(rand(0, 90));
            
            $descriptions = [
                'Fatura ödemesi',
                'Peşin ödeme',
                'Kısmi ödeme',
                'Taksit ödemesi',
                'İndirimli ödeme',
                'Hızlı ödeme',
                'Online ödeme',
                'Kart ile ödeme',
                'Havale ile ödeme',
                'EFT ile ödeme'
            ];
            
            Collection::create([
                'customer_id' => $customer->id,
                'collection_type' => $collectionType,
                'transaction_date' => $transactionDate,
                'amount' => $amount,
                'currency' => $currency,
                'description' => $descriptions[array_rand($descriptions)],
                'is_active' => rand(0, 10) > 1, // %90 aktif
                'created_at' => $transactionDate,
                'updated_at' => $transactionDate
            ]);
        }
        
        $this->command->info('50 collection records created successfully!');
    }
}

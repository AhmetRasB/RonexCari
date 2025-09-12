<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CheckBill;
use App\Models\Customer;
use App\Models\Supplier;
use Carbon\Carbon;

class CheckBillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        
        if ($customers->isEmpty() && $suppliers->isEmpty()) {
            $this->command->warn('No active customers or suppliers found. Please run CustomerSeeder and SupplierSeeder first.');
            return;
        }

        $types = ['received', 'given'];
        $currencies = ['TRY', 'USD', 'EUR'];
        $statuses = ['portfolio', 'cashed', 'returned', 'cancelled'];
        
        $bankNames = [
            'Akbank', 'Garanti BBVA', 'İş Bankası', 'Yapı Kredi', 'Ziraat Bankası',
            'Halkbank', 'VakıfBank', 'Denizbank', 'QNB Finansbank', 'Türkiye Finans'
        ];
        
        $bankBranches = [
            'Merkez Şube', 'Kadıköy Şubesi', 'Beşiktaş Şubesi', 'Şişli Şubesi',
            'Beyoğlu Şubesi', 'Üsküdar Şubesi', 'Fatih Şubesi', 'Bakırköy Şubesi'
        ];
        
        // 60 çek/senet kaydı oluştur
        for ($i = 0; $i < 60; $i++) {
            $type = $types[array_rand($types)];
            $currency = $currencies[array_rand($currencies)];
            $status = $statuses[array_rand($statuses)];
            
            // Para birimine göre tutar aralığı
            $amount = match($currency) {
                'TRY' => rand(500, 100000) + (rand(0, 99) / 100),
                'USD' => rand(100, 5000) + (rand(0, 99) / 100),
                'EUR' => rand(100, 5000) + (rand(0, 99) / 100),
                default => rand(500, 10000) + (rand(0, 99) / 100)
            };
            
            // Son 6 ay içinde rastgele işlem tarihi
            $transactionDate = Carbon::now()->subDays(rand(0, 180));
            
            // Vade tarihi işlem tarihinden 1-90 gün sonra
            $dueDate = $transactionDate->copy()->addDays(rand(1, 90));
            
            $bankName = $bankNames[array_rand($bankNames)];
            $bankBranch = $bankBranches[array_rand($bankBranches)];
            
            $descriptions = [
                'Fatura ödemesi için çek',
                'Taksit ödemesi',
                'Peşin ödeme çeki',
                'Kısmi ödeme',
                'İndirimli ödeme çeki',
                'Hızlı ödeme',
                'Online çek',
                'Kart ile ödeme çeki',
                'Havale ile ödeme',
                'EFT ile ödeme çeki'
            ];
            
            $checkBillData = [
                'type' => $type,
                'bank_name' => $bankName,
                'bank_branch' => $bankBranch,
                'account_number' => rand(1000000000, 9999999999),
                'original_owner' => fake('tr_TR')->name(),
                'check_number' => 'CHK-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                'transaction_date' => $transactionDate,
                'due_date' => $dueDate,
                'amount' => $amount,
                'currency' => $currency,
                'status' => $status,
                'description' => $descriptions[array_rand($descriptions)],
                'is_active' => rand(0, 10) > 1, // %90 aktif
                'created_at' => $transactionDate,
                'updated_at' => $transactionDate
            ];
            
            // Müşteri veya tedarikçi seç
            if ($type === 'received' && !$customers->isEmpty()) {
                $checkBillData['customer_id'] = $customers->random()->id;
            } elseif ($type === 'given' && !$suppliers->isEmpty()) {
                $checkBillData['supplier_id'] = $suppliers->random()->id;
            } else {
                // Eğer uygun müşteri/tedarikçi yoksa rastgele seç
                if (!$customers->isEmpty()) {
                    $checkBillData['customer_id'] = $customers->random()->id;
                }
                if (!$suppliers->isEmpty()) {
                    $checkBillData['supplier_id'] = $suppliers->random()->id;
                }
            }
            
            CheckBill::create($checkBillData);
        }
        
        $this->command->info('60 check/bill records created successfully!');
    }
}

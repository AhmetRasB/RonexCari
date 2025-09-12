<?php

namespace Database\Seeders;

use App\Models\Expense;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expenses = [
            [
                'name' => 'Elektrik Faturası',
                'amount' => 1250.50,
                'description' => 'Ocak ayı elektrik faturası',
                'expense_date' => now()->subDays(5),
                'is_active' => true,
            ],
            [
                'name' => 'Su Faturası',
                'amount' => 180.75,
                'description' => 'Ocak ayı su faturası',
                'expense_date' => now()->subDays(3),
                'is_active' => true,
            ],
            [
                'name' => 'İnternet Faturası',
                'amount' => 89.90,
                'description' => 'Aylık internet aboneliği',
                'expense_date' => now()->subDays(1),
                'is_active' => true,
            ],
            [
                'name' => 'Temizlik Malzemeleri',
                'amount' => 450.00,
                'description' => 'Ofis temizlik malzemeleri',
                'expense_date' => now()->subDays(7),
                'is_active' => true,
            ],
            [
                'name' => 'Kırtasiye Malzemeleri',
                'amount' => 320.25,
                'description' => 'Ofis kırtasiye ihtiyaçları',
                'expense_date' => now()->subDays(10),
                'is_active' => true,
            ],
            [
                'name' => 'Bakım ve Onarım',
                'amount' => 750.00,
                'description' => 'Bilgisayar bakım ve onarım',
                'expense_date' => now()->subDays(15),
                'is_active' => true,
            ],
            [
                'name' => 'Kargo Ücreti',
                'amount' => 45.50,
                'description' => 'Müşteri gönderileri için kargo',
                'expense_date' => now()->subDays(2),
                'is_active' => true,
            ],
            [
                'name' => 'Yakıt Gideri',
                'amount' => 680.00,
                'description' => 'Firma aracı yakıt gideri',
                'expense_date' => now()->subDays(4),
                'is_active' => true,
            ],
        ];

        foreach ($expenses as $expense) {
            Expense::create($expense);
        }
    }
}
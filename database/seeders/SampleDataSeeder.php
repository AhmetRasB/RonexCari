<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Service;
use App\Models\Employee;
use App\Models\Expense;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'ahmetrasimbayhan@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('123456789'),
            ]
        );
        // Create sample customers
        Customer::create([
            'name' => 'Ahmet Yılmaz',
            'company_name' => 'Yılmaz Ticaret A.Ş.',
            'email' => 'ahmet@yilmazticaret.com',
            'phone' => '+90 212 555 0101',
            'address' => 'İstanbul, Türkiye',
            'tax_number' => '1234567890',
            'contact_person' => 'Ahmet Yılmaz',
            'notes' => 'VIP müşteri',
            'is_active' => true,
        ]);

        Customer::create([
            'name' => 'Fatma Demir',
            'company_name' => 'Demir İnşaat Ltd.',
            'email' => 'fatma@demirinsaat.com',
            'phone' => '+90 216 555 0202',
            'address' => 'Ankara, Türkiye',
            'tax_number' => '0987654321',
            'contact_person' => 'Fatma Demir',
            'notes' => 'Düzenli müşteri',
            'is_active' => false,
        ]);

        Customer::create([
            'name' => 'Mehmet Özkan',
            'company_name' => 'Özkan Teknoloji A.Ş.',
            'email' => 'mehmet@ozkanteknoloji.com',
            'phone' => '+90 232 555 0303',
            'address' => 'İzmir, Türkiye',
            'tax_number' => '1122334455',
            'contact_person' => 'Mehmet Özkan',
            'notes' => 'Yeni müşteri',
            'is_active' => true,
        ]);

        // Create sample suppliers
        Supplier::create([
            'name' => 'Mehmet Kaya',
            'company_name' => 'Kaya Malzemeleri A.Ş.',
            'email' => 'mehmet@kayamalzemeleri.com',
            'phone' => '+90 312 555 0303',
            'address' => 'İzmir, Türkiye',
            'tax_number' => '1122334455',
            'contact_person' => 'Mehmet Kaya',
            'notes' => 'Güvenilir tedarikçi',
            'is_active' => true,
        ]);

        Supplier::create([
            'name' => 'Ayşe Özkan',
            'company_name' => 'Özkan Hizmetler Ltd.',
            'email' => 'ayse@ozkanhizmetler.com',
            'phone' => '+90 232 555 0404',
            'address' => 'Bursa, Türkiye',
            'tax_number' => '5566778899',
            'contact_person' => 'Ayşe Özkan',
            'notes' => 'Hızlı teslimat',
            'is_active' => true,
        ]);

        // Create sample products
        Product::create([
            'name' => 'Laptop Bilgisayar',
            'sku' => 'LAP-001',
            'description' => 'Yüksek performanslı iş bilgisayarı',
            'price' => 15000.00,
            'cost' => 12000.00,
            'stock_quantity' => 25,
            'min_stock_level' => 5,
            'category' => 'Elektronik',
            'brand' => 'TechBrand',
            'unit' => 'adet',
            'barcode' => '1234567890123',
            'is_active' => true,
        ]);
        
        Product::create([
            'name' => 'Ofis Sandalyesi',
            'sku' => 'SAN-001',
            'description' => 'Ergonomik ofis sandalyesi',
            'price' => 2500.00,
            'cost' => 1800.00,
            'stock_quantity' => 50,
            'min_stock_level' => 10,
            'category' => 'Mobilya',
            'brand' => 'ComfortSeat',
            'unit' => 'adet',
            'barcode' => '2345678901234',
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Yazıcı',
            'sku' => 'YAZ-001',
            'description' => 'Lazer yazıcı',
            'price' => 800.00,
            'cost' => 600.00,
            'stock_quantity' => 15,
            'min_stock_level' => 3,
            'category' => 'Elektronik',
            'brand' => 'PrintTech',
            'unit' => 'adet',
            'barcode' => '3456789012345',
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Masa',
            'sku' => 'MAS-001',
            'description' => 'Ahşap çalışma masası',
            'price' => 1200.00,
            'cost' => 800.00,
            'stock_quantity' => 30,
            'min_stock_level' => 5,
            'category' => 'Mobilya',
            'brand' => 'WoodCraft',
            'unit' => 'adet',
            'barcode' => '4567890123456',
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Klavye',
            'sku' => 'KLA-001',
            'description' => 'Mekanik klavye',
            'price' => 450.00,
            'cost' => 300.00,
            'stock_quantity' => 40,
            'min_stock_level' => 8,
            'category' => 'Elektronik',
            'brand' => 'KeyMaster',
            'unit' => 'adet',
            'barcode' => '5678901234567',
            'is_active' => true,
        ]);

        // Create sample services
        Service::create([
            'name' => 'Web Tasarım Hizmeti',
            'code' => 'WEB-001',
            'description' => 'Profesyonel web sitesi tasarımı',
            'price' => 5000.00,
            'category' => 'Teknoloji',
            'unit' => 'proje',
            'is_active' => true,
        ]);

        Service::create([
            'name' => 'Muhasebe Danışmanlığı',
            'code' => 'MUH-001',
            'description' => 'Aylık muhasebe danışmanlık hizmeti',
            'price' => 2000.00,
            'category' => 'Danışmanlık',
            'unit' => 'ay',
            'is_active' => true,
        ]);

        // Create sample employees
        Employee::create([
            'name' => 'Ali Veli',
            'email' => 'ali@company.com',
            'phone' => '+90 555 123 4567',
            'position' => 'Satış Temsilcisi',
            'department' => 'Satış',
            'salary' => 8000.00,
            'hire_date' => '2023-01-15',
            'is_active' => true,
        ]);

        Employee::create([
            'name' => 'Zeynep Kaya',
            'email' => 'zeynep@company.com',
            'phone' => '+90 555 987 6543',
            'position' => 'Muhasebe Uzmanı',
            'department' => 'Muhasebe',
            'salary' => 9000.00,
            'hire_date' => '2022-06-01',
            'is_active' => true,
        ]);

        // Create sample expenses
        Expense::create([
            'name' => 'Ofis Kira Ödemesi',
            'description' => 'Aylık ofis kira ödemesi',
            'amount' => 15000.00,
            'expense_date' => now()->format('Y-m-d'),
            'category' => 'Kira',
            'payment_method' => 'Banka Transferi',
            'receipt_number' => 'RCP-001',
            'employee_id' => 1,
        ]);

        Expense::create([
            'name' => 'Elektrik Faturası',
            'description' => 'Aylık elektrik faturası',
            'amount' => 2500.00,
            'expense_date' => now()->format('Y-m-d'),
            'category' => 'Fatura',
            'payment_method' => 'Kredi Kartı',
            'receipt_number' => 'RCP-002',
            'employee_id' => 2,
        ]);
    }
}
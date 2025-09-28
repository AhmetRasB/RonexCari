<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductColorVariant;

class TestProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Test ürünleri - 20 adet
        $testProducts = [
            // Gömlek Kategorisi (Ronex1 için)
            [
                'name' => 'Klasik Beyaz Gömlek',
                'product_code' => 'GOM-001',
                'category' => 'Gömlek',
                'brand' => 'Ronex',
                'size' => 'M',
                'color' => 'Beyaz',
                'sale_price' => 450,
                'purchase_price' => 280,
                'initial_stock' => 5, // Kritik stok uyarısı için düşük
                'critical_stock' => 10,
                'colors' => ['Beyaz', 'Mavi', 'Pembe']
            ],
            [
                'name' => 'Casual Mavi Gömlek',
                'product_code' => 'GOM-002',
                'category' => 'Gömlek',
                'brand' => 'Premium',
                'size' => 'L',
                'color' => 'Mavi',
                'sale_price' => 380,
                'purchase_price' => 240,
                'initial_stock' => 30,
                'critical_stock' => 8,
                'colors' => ['Mavi', 'Siyah', 'Gri']
            ],
            [
                'name' => 'Polo Kırmızı Gömlek',
                'product_code' => 'GOM-003',
                'category' => 'Gömlek',
                'brand' => 'Classic',
                'size' => 'XL',
                'color' => 'Kırmızı',
                'sale_price' => 320,
                'purchase_price' => 200,
                'initial_stock' => 25,
                'critical_stock' => 5,
                'colors' => ['Kırmızı', 'Beyaz', 'Siyah', 'Mavi']
            ],
            [
                'name' => 'Formal Siyah Gömlek',
                'product_code' => 'GOM-004',
                'category' => 'Gömlek',
                'brand' => 'Elegant',
                'size' => 'S',
                'color' => 'Siyah',
                'sale_price' => 520,
                'purchase_price' => 320,
                'initial_stock' => 40,
                'critical_stock' => 12,
                'colors' => ['Siyah', 'Beyaz']
            ],
            [
                'name' => 'Uzun Kollu Gri Gömlek',
                'product_code' => 'GOM-005',
                'category' => 'Gömlek',
                'brand' => 'Modern',
                'size' => 'M',
                'color' => 'Gri',
                'sale_price' => 480,
                'purchase_price' => 300,
                'initial_stock' => 35,
                'critical_stock' => 8,
                'colors' => ['Gri', 'Beyaz', 'Mavi', 'Siyah']
            ],

            // Ceket Kategorisi (Ronex2 için)
            [
                'name' => 'Klasik Blazer',
                'product_code' => 'CEK-001',
                'category' => 'Ceket',
                'brand' => 'Ronex',
                'size' => 'L',
                'color' => 'Navy',
                'sale_price' => 1200,
                'purchase_price' => 800,
                'initial_stock' => 15,
                'critical_stock' => 3,
                'colors' => ['Navy', 'Siyah', 'Gri']
            ],
            [
                'name' => 'Spor Ceket',
                'product_code' => 'CEK-002',
                'category' => 'Ceket',
                'brand' => 'Style',
                'size' => 'M',
                'color' => 'Siyah',
                'sale_price' => 850,
                'purchase_price' => 550,
                'initial_stock' => 20,
                'critical_stock' => 5,
                'colors' => ['Siyah', 'Mavi', 'Gri']
            ],
            [
                'name' => 'Deri Ceket',
                'product_code' => 'CEK-003',
                'category' => 'Ceket',
                'brand' => 'Luxury',
                'size' => 'XL',
                'color' => 'Kahverengi',
                'sale_price' => 1800,
                'purchase_price' => 1200,
                'initial_stock' => 8,
                'critical_stock' => 2,
                'colors' => ['Kahverengi', 'Siyah']
            ],
            [
                'name' => 'Kışlık Ceket',
                'product_code' => 'CEK-004',
                'category' => 'Ceket',
                'brand' => 'Premium',
                'size' => 'L',
                'color' => 'Bordo',
                'sale_price' => 950,
                'purchase_price' => 600,
                'initial_stock' => 12,
                'critical_stock' => 3,
                'colors' => ['Bordo', 'Siyah', 'Navy']
            ],
            [
                'name' => 'Yazlık Ceket',
                'product_code' => 'CEK-005',
                'category' => 'Ceket',
                'brand' => 'Fashion',
                'size' => 'M',
                'color' => 'Beyaz',
                'sale_price' => 750,
                'purchase_price' => 480,
                'initial_stock' => 18,
                'critical_stock' => 4,
                'colors' => ['Beyaz', 'Açık Mavi', 'Açık Gri']
            ],

            // Takım Elbise Kategorisi (Ronex2 için)
            [
                'name' => 'İş Takımı',
                'product_code' => 'TAK-001',
                'category' => 'Takım Elbise',
                'brand' => 'Ronex',
                'size' => 'L',
                'color' => 'Siyah',
                'sale_price' => 2500,
                'purchase_price' => 1600,
                'initial_stock' => 10,
                'critical_stock' => 2,
                'colors' => ['Siyah', 'Navy', 'Gri']
            ],
            [
                'name' => 'Resmi Takım',
                'product_code' => 'TAK-002',
                'category' => 'Takım Elbise',
                'brand' => 'Elegant',
                'size' => 'M',
                'color' => 'Navy',
                'sale_price' => 2800,
                'purchase_price' => 1800,
                'initial_stock' => 8,
                'critical_stock' => 2,
                'colors' => ['Navy', 'Siyah']
            ],
            [
                'name' => 'Casual Takım',
                'product_code' => 'TAK-003',
                'category' => 'Takım Elbise',
                'brand' => 'Modern',
                'size' => 'XL',
                'color' => 'Gri',
                'sale_price' => 2200,
                'purchase_price' => 1400,
                'initial_stock' => 12,
                'critical_stock' => 3,
                'colors' => ['Gri', 'Mavi', 'Kahverengi']
            ],
            [
                'name' => 'Akşam Takımı',
                'product_code' => 'TAK-004',
                'category' => 'Takım Elbise',
                'brand' => 'Luxury',
                'size' => 'L',
                'color' => 'Siyah',
                'sale_price' => 3200,
                'purchase_price' => 2000,
                'initial_stock' => 6,
                'critical_stock' => 1,
                'colors' => ['Siyah']
            ],
            [
                'name' => 'Günlük Takım',
                'product_code' => 'TAK-005',
                'category' => 'Takım Elbise',
                'brand' => 'Style',
                'size' => 'M',
                'color' => 'Mavi',
                'sale_price' => 1900,
                'purchase_price' => 1200,
                'initial_stock' => 15,
                'critical_stock' => 4,
                'colors' => ['Mavi', 'Gri', 'Kahverengi']
            ],

            // Pantalon Kategorisi (Ronex2 için)
            [
                'name' => 'Klasik Pantalon',
                'product_code' => 'PAN-001',
                'category' => 'Pantalon',
                'brand' => 'Ronex',
                'size' => '32',
                'color' => 'Siyah',
                'sale_price' => 650,
                'purchase_price' => 420,
                'initial_stock' => 25,
                'critical_stock' => 6,
                'colors' => ['Siyah', 'Navy', 'Gri', 'Kahverengi']
            ],
            [
                'name' => 'Spor Pantalon',
                'product_code' => 'PAN-002',
                'category' => 'Pantalon',
                'brand' => 'Fashion',
                'size' => '34',
                'color' => 'Mavi',
                'sale_price' => 480,
                'purchase_price' => 310,
                'initial_stock' => 30,
                'critical_stock' => 8,
                'colors' => ['Mavi', 'Siyah', 'Gri']
            ],
            [
                'name' => 'Jean Pantalon',
                'product_code' => 'PAN-003',
                'category' => 'Pantalon',
                'brand' => 'Trend',
                'size' => '36',
                'color' => 'Mavi',
                'sale_price' => 420,
                'purchase_price' => 270,
                'initial_stock' => 40,
                'critical_stock' => 10,
                'colors' => ['Mavi', 'Siyah', 'Gri', 'Beyaz']
            ],
            [
                'name' => 'Chino Pantalon',
                'product_code' => 'PAN-004',
                'category' => 'Pantalon',
                'brand' => 'Classic',
                'size' => '38',
                'color' => 'Kahverengi',
                'sale_price' => 550,
                'purchase_price' => 350,
                'initial_stock' => 20,
                'critical_stock' => 5,
                'colors' => ['Kahverengi', 'Navy', 'Gri']
            ],
            [
                'name' => 'Formal Pantalon',
                'product_code' => 'PAN-005',
                'category' => 'Pantalon',
                'brand' => 'Elegant',
                'size' => '40',
                'color' => 'Navy',
                'sale_price' => 720,
                'purchase_price' => 460,
                'initial_stock' => 18,
                'critical_stock' => 4,
                'colors' => ['Navy', 'Siyah', 'Gri']
            ]
        ];

        foreach ($testProducts as $index => $productData) {
            // Benzersiz SKU oluştur
            $uniqueSku = $productData['product_code'] . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            
            // Account ID'yi belirle
            $accountId = null;
            if ($productData['category'] === 'Gömlek') {
                $ronex1 = \App\Models\Account::where('code', 'RONEX1')->first();
                $accountId = $ronex1 ? $ronex1->id : null;
            } else {
                $ronex2 = \App\Models\Account::where('code', 'RONEX2')->first();
                $accountId = $ronex2 ? $ronex2->id : null;
            }
            
            // Ana ürünü oluştur
            $product = Product::create([
                'account_id' => $accountId,
                'name' => $productData['name'],
                'sku' => $uniqueSku,
                'unit' => 'adet',
                'price' => $productData['sale_price'],
                'cost' => $productData['purchase_price'],
                'category' => $productData['category'],
                'brand' => $productData['brand'],
                'barcode' => 'BC' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                'description' => "{$productData['category']} kategorisinde {$productData['brand']} markalı {$productData['color']} renkli {$productData['size']} beden {$productData['name']}",
                'stock_quantity' => $productData['initial_stock'],
                'initial_stock' => $productData['initial_stock'],
                'critical_stock' => $productData['critical_stock'],
                'is_active' => true,
            ]);

            // Renk varyantlarını oluştur
            if (isset($productData['colors']) && count($productData['colors']) > 1) {
                $stockPerColor = intval($productData['initial_stock'] / count($productData['colors']));
                $remainingStock = $productData['initial_stock'] % count($productData['colors']);
                
                foreach ($productData['colors'] as $index => $color) {
                    $stockForThisColor = $stockPerColor + ($index < $remainingStock ? 1 : 0);
                    $criticalForThisColor = intval($productData['critical_stock'] / count($productData['colors']));
                    
                    ProductColorVariant::create([
                        'product_id' => $product->id,
                        'color' => $color,
                        'stock_quantity' => $stockForThisColor,
                        'critical_stock' => $criticalForThisColor,
                        'is_active' => true,
                    ]);
                }
            }
        }

        $this->command->info("✅ 20 test ürünü başarıyla eklendi!");
        $this->command->info("📊 Kategoriler:");
        $this->command->info("   • Gömlek: 5 ürün (Ronex1 için)");
        $this->command->info("   • Ceket: 5 ürün (Ronex2 için)");
        $this->command->info("   • Takım Elbise: 5 ürün (Ronex2 için)");
        $this->command->info("   • Pantalon: 5 ürün (Ronex2 için)");
        $this->command->info("🎨 Renk varyantları ile birlikte toplam " . ProductColorVariant::count() . " renk seçeneği eklendi!");
    }
}

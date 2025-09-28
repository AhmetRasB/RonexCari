<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Service;

class ProductServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Renkler
        $colors = [
            'Siyah', 'Beyaz', 'Kırmızı', 'Mavi', 'Yeşil', 'Sarı', 'Mor', 'Turuncu', 'Pembe', 'Gri',
            'Kahverengi', 'Lacivert', 'Bordo', 'Turkuaz', 'Altın', 'Gümüş', 'Bej', 'Krem', 'Haki', 'Navy',
            'Füme', 'Antrasit', 'Koyu Mavi', 'Açık Mavi', 'Koyu Yeşil', 'Açık Yeşil', 'Koyu Kırmızı', 'Açık Kırmızı',
            'Koyu Mor', 'Açık Mor', 'Koyu Sarı', 'Açık Sarı', 'Koyu Turuncu', 'Açık Turuncu', 'Koyu Pembe', 'Açık Pembe',
            'Koyu Gri', 'Açık Gri', 'Koyu Kahverengi', 'Açık Kahverengi', 'Koyu Lacivert', 'Açık Lacivert',
            'Koyu Bordo', 'Açık Bordo', 'Koyu Turkuaz', 'Açık Turkuaz', 'Koyu Altın', 'Açık Altın',
            'Koyu Gümüş', 'Açık Gümüş', 'Koyu Bej', 'Açık Bej', 'Koyu Krem', 'Açık Krem', 'Koyu Haki', 'Açık Haki'
        ];

        // Bedenler
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', '28', '30', '32', '34', '36', '38', '40', '42', '44', '46', '48', '50', '52'];

        // Markalar
        $brands = ['Ronex', 'Premium', 'Classic', 'Modern', 'Elegant', 'Style', 'Fashion', 'Trend', 'Luxury', 'Basic'];

        // Kategoriler
        $categories = [
            'Gömlek' => ['Klasik Gömlek', 'Casual Gömlek', 'Formal Gömlek', 'Polo Gömlek', 'Uzun Kollu Gömlek', 'Kısa Kollu Gömlek'],
            'Ceket' => ['Blazer', 'Spor Ceket', 'Klasik Ceket', 'Deri Ceket', 'Kışlık Ceket', 'Yazlık Ceket'],
            'Takım Elbise' => ['İş Takımı', 'Resmi Takım', 'Casual Takım', 'Akşam Takımı', 'Günlük Takım'],
            'Pantalon' => ['Klasik Pantalon', 'Spor Pantalon', 'Jean Pantalon', 'Chino Pantalon', 'Formal Pantalon', 'Casual Pantalon'],
            'Aksesuar' => ['Kravat', 'Kemer', 'Çanta', 'Cüzdan', 'Saat', 'Gözlük', 'Şapka', 'Eldiven', 'Atkı', 'Çorap']
        ];

        // Hizmet kategorileri
        $serviceCategories = [
            'Kargo' => ['Hızlı Kargo', 'Standart Kargo', 'Özel Kargo', 'Uluslararası Kargo'],
            'Reklamasyon' => ['Ürün Değişimi', 'Para İadesi', 'Tamir Hizmeti', 'Garanti Hizmeti'],
            'Danışmanlık' => ['Stil Danışmanlığı', 'Giyim Danışmanlığı', 'Renk Danışmanlığı'],
            'Bakım' => ['Ütüleme', 'Temizlik', 'Onarım', 'Bakım Hizmeti']
        ];

        $productCounter = 1;
        $serviceCounter = 1;

        // Ürünler ekle
        foreach ($categories as $mainCategory => $subCategories) {
            foreach ($subCategories as $subCategory) {
                foreach ($brands as $brand) {
                    foreach ($colors as $color) {
                        foreach ($sizes as $size) {
                            if ($productCounter > 500) break 4; // 500 ürün limiti

                            $productCode = 'PRD-' . str_pad($productCounter + 14, 6, '0', STR_PAD_LEFT); // Mevcut 14 ürünü atla
                            
                            Product::create([
                                'name' => "{$subCategory} - {$brand} - {$color} - {$size}",
                                'product_code' => $productCode,
                                'unit' => 'adet',
                                'sale_price' => rand(50, 2000), // 50-2000 TL arası rastgele fiyat
                                'purchase_price' => rand(30, 1500), // 30-1500 TL arası rastgele fiyat
                                'currency' => 'TRY',
                                'vat_rate' => 20,
                                'category' => $mainCategory,
                                'brand' => $brand,
                                'size' => $size,
                                'color' => $color,
                                'barcode' => 'BC' . str_pad($productCounter + 14, 10, '0', STR_PAD_LEFT),
                                'supplier_code' => 'SUP-' . str_pad(rand(1, 10), 3, '0', STR_PAD_LEFT),
                                'gtip_code' => 'GTIP' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                                'class_code' => 'CLS' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT),
                                'description' => "{$mainCategory} kategorisinde {$brand} markalı {$color} renkli {$size} beden {$subCategory}",
                                'initial_stock' => rand(0, 100),
                                'critical_stock' => rand(5, 20),
                                'is_active' => true,
                            ]);

                            $productCounter++;
                        }
                    }
                }
            }
        }

        // Hizmetler ekle
        foreach ($serviceCategories as $mainCategory => $subCategories) {
            foreach ($subCategories as $subCategory) {
                $serviceCode = 'SRV-' . str_pad($serviceCounter + 3, 6, '0', STR_PAD_LEFT); // Mevcut 3 hizmeti atla
                
                Service::create([
                    'name' => $subCategory,
                    'code' => $serviceCode,
                    'category' => $mainCategory,
                    'price' => rand(10, 500), // 10-500 TL arası rastgele fiyat
                    'currency' => 'TRY',
                    'vat_rate' => 20,
                    'description' => "{$mainCategory} kategorisinde {$subCategory} hizmeti",
                    'is_active' => true,
                ]);

                $serviceCounter++;
            }
        }

        $this->command->info("✅ {$productCounter} ürün ve {$serviceCounter} hizmet başarıyla eklendi!");
    }
}
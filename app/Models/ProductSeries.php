<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductSeries extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'category',
        'brand',
        'cost',
        'price',
        'image',
        'series_type',
        'series_size',
        'stock_quantity',
        'critical_stock',
        'is_active'
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Seri içindeki bedenler
     */
    public function seriesItems()
    {
        return $this->hasMany(ProductSeriesItem::class);
    }

    /**
     * Toplam ürün sayısı (seri sayısı * seri boyutu)
     */
    public function getTotalProductCountAttribute()
    {
        return $this->stock_quantity * $this->series_size;
    }

    /**
     * Seri boyutuna göre varsayılan bedenler (FixedSeriesSetting'den)
     */
    public static function getDefaultSizesForSeries($seriesSize)
    {
        // Önce veritabanından ayarları kontrol et
        $setting = \App\Models\FixedSeriesSetting::where('series_size', $seriesSize)->first();
        if ($setting) {
            return $setting->sizes;
        }
        
        // Eğer veritabanında yoksa varsayılan değerleri döndür
        $defaultSizes = [
            5 => ['XS', 'S', 'M', 'L', 'XL'],
            6 => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            7 => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'],
        ];

        return $defaultSizes[$seriesSize] ?? [];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductSeries extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'sku',
        'barcode',
        'description',
        'category',
        'brand',
        'cost',
        'cost_currency',
        'price',
        'price_currency',
        // 'currency' kept historically; not used for new data but may exist in DB
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
     * Seri renk varyantları
     */
    public function colorVariants()
    {
        return $this->hasMany(ProductSeriesColorVariant::class);
    }

    /**
     * Toplam ürün sayısı (seri sayısı * seri boyutu)
     */
    public function getTotalProductCountAttribute()
    {
        return $this->stock_quantity * $this->series_size;
    }

    /**
     * Görsel URL'ini getir
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        
        try {
            // Görsel yolu zaten tam yol ise (uploads/products/ ile başlıyorsa)
            if (substr($this->image, 0, 8) === 'uploads/') {
                return asset($this->image);
            }
            
            // Storage klasöründeki görseller için
            $imagePath = 'storage/' . $this->image;
            
            // Dosya var mı kontrol et
            if (file_exists(public_path($imagePath))) {
                return asset($imagePath);
            }
            
            // Fallback olarak storage URL'i dene
            return \Storage::url($this->image);
        } catch (\Exception $e) {
            // Hata durumunda null döndür
            \Log::error('Image URL generation failed', [
                'image' => $this->image,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get the account that owns this product series
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}

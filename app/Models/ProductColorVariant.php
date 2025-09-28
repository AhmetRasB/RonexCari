<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductColorVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'color',
        'color_code',
        'stock_quantity',
        'critical_stock',
        'image',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the product that owns this color variant
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the image URL for this color variant
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
            \Log::error('Color variant image URL generation failed', [
                'image' => $this->image,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Check if this color variant is low on stock
     */
    public function getIsLowStockAttribute()
    {
        return $this->stock_quantity <= $this->critical_stock;
    }
}
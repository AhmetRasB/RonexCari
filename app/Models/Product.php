<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'unit',
        'cost',
        'price',
        'category',
        'brand',
        'size',
        'color',
        'barcode',
        'description',
        'image',
        'initial_stock',
        'critical_stock',
        'is_active'
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Görsel URL'ini getir
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        
        // Linux Plesk ortamında daha güvenilir çalışması için
        $imagePath = 'storage/' . $this->image;
        
        // Dosya var mı kontrol et
        if (file_exists(public_path($imagePath))) {
            return asset($imagePath);
        }
        
        // Fallback olarak storage URL'i dene
        return \Storage::url($this->image);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}

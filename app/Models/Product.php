<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'product_code',
        'unit',
        'purchase_price',
        'sale_price',
        'currency',
        'vat_rate',
        'category',
        'brand',
        'size',
        'color',
        'barcode',
        'supplier_code',
        'gtip_code',
        'class_code',
        'description',
        'image',
        'initial_stock',
        'critical_stock',
        'is_saleable',
        'is_active'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'is_saleable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}

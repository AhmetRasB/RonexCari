<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_service_name',
        'description',
        'selected_color',
        'product_id',
        'color_variant_id',
        'product_type',
        'quantity',
        'unit',
        'unit_price',
        'unit_currency',
        'tax_rate',
        'discount_rate',
        'line_total',
        'total',
        'sort_order'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function colorVariant(): BelongsTo
    {
        return $this->belongsTo(ProductColorVariant::class, 'color_variant_id');
    }
}

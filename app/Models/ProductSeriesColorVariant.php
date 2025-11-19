<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSeriesColorVariant extends Model
{
    protected $fillable = [
        'product_series_id',
        'color',
        'barcode',
        'qr_code_value',
        'stock_quantity',
        'critical_stock',
        'is_active'
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
        'critical_stock' => 'integer',
        'is_active' => 'boolean'
    ];

    public function productSeries(): BelongsTo
    {
        return $this->belongsTo(ProductSeries::class);
    }
}

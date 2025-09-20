<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductSeriesItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_series_id',
        'size',
        'quantity_per_series'
    ];

    /**
     * Hangi seriye ait
     */
    public function productSeries()
    {
        return $this->belongsTo(ProductSeries::class);
    }
}

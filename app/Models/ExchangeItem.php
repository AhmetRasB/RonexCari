<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeItem extends Model
{
    protected $fillable = [
        'exchange_id',
        'original_item_id',
        'new_item_id',
        'original_amount',
        'new_amount',
        'difference',
        'notes'
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'new_amount' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function exchange(): BelongsTo
    {
        return $this->belongsTo(Exchange::class);
    }

    public function originalItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'original_item_id');
    }

    public function newItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'new_item_id');
    }
}

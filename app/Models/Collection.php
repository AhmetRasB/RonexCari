<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Collection extends Model
{
    protected $fillable = [
        'customer_id',
        'collection_type',
        'transaction_date',
        'amount',
        'currency',
        'description',
        'is_active'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getCollectionTypeTextAttribute(): string
    {
        return match($this->collection_type) {
            'nakit' => 'Nakit',
            'banka' => 'Banka',
            'kredi_karti' => 'Kredi Kartı',
            'havale' => 'Havale',
            'eft' => 'EFT',
            default => $this->collection_type
        };
    }
}

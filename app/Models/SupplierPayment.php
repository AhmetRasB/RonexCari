<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    protected $fillable = [
        'account_id',
        'supplier_id',
        'payment_type',
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

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function getPaymentTypeTextAttribute(): string
    {
        return match($this->payment_type) {
            'nakit' => 'Nakit',
            'banka' => 'Banka',
            'kredi_karti' => 'Kredi Kartı',
            'havale' => 'Havale',
            'eft' => 'EFT',
            default => $this->payment_type
        };
    }
}

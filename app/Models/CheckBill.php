<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'customer_id',
        'supplier_id',
        'type',
        'bank_name',
        'bank_branch',
        'account_number',
        'original_owner',
        'check_number',
        'transaction_date',
        'due_date',
        'amount',
        'currency',
        'status',
        'description',
        'is_active',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the account that owns the check bill.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the customer that owns the check bill.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the supplier that owns the check bill.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Scope a query to only include active check bills.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include received check bills.
     */
    public function scopeReceived($query)
    {
        return $query->where('type', 'received');
    }

    /**
     * Scope a query to only include given check bills.
     */
    public function scopeGiven($query)
    {
        return $query->where('type', 'given');
    }
}

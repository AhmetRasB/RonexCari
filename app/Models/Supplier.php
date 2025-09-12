<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'address',
        'tax_number',
        'contact_person',
        'notes',
        'balance',
        'balance_try',
        'balance_usd',
        'balance_eur',
        'paid_amount_try',
        'paid_amount_usd',
        'paid_amount_eur',
        'last_payment_date',
        'is_active'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'balance_try' => 'decimal:2',
        'balance_usd' => 'decimal:2',
        'balance_eur' => 'decimal:2',
        'paid_amount_try' => 'decimal:2',
        'paid_amount_usd' => 'decimal:2',
        'paid_amount_eur' => 'decimal:2',
        'last_payment_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function purchaseInvoices()
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class);
    }

    // Accessors for payment tracking
    public function getRemainingBalanceTryAttribute()
    {
        return $this->balance_try - $this->paid_amount_try;
    }

    public function getRemainingBalanceUsdAttribute()
    {
        return $this->balance_usd - $this->paid_amount_usd;
    }

    public function getRemainingBalanceEurAttribute()
    {
        return $this->balance_eur - $this->paid_amount_eur;
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->remaining_balance_try <= 0 && $this->remaining_balance_usd <= 0 && $this->remaining_balance_eur <= 0) {
            return 'paid';
        } elseif ($this->paid_amount_try > 0 || $this->paid_amount_usd > 0 || $this->paid_amount_eur > 0) {
            return 'partial';
        } else {
            return 'pending';
        }
    }
}

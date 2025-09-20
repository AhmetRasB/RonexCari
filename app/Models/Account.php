<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'company_name',
        'address',
        'city',
        'district',
        'postal_code',
        'phone',
        'email'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get all invoices for this account
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all purchase invoices for this account
     */
    public function purchaseInvoices()
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    /**
     * Get all expenses for this account
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get all collections for this account
     */
    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    /**
     * Get all check bills for this account
     */
    public function checkBills()
    {
        return $this->hasMany(CheckBill::class);
    }

    /**
     * Scope a query to only include active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

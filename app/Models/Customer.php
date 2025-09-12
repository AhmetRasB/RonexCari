<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
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
        'is_active'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'balance_try' => 'decimal:2',
        'balance_usd' => 'decimal:2',
        'balance_eur' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }
}

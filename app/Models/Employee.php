<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'monthly_salary',
        'salary_day',
        'accumulated_salary',
        'paid_amount',
        'last_payment_date',
        'is_active'
    ];

    protected $casts = [
        'monthly_salary' => 'decimal:2',
        'accumulated_salary' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'last_payment_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function getRemainingSalaryAttribute()
    {
        return $this->accumulated_salary - $this->paid_amount;
    }

    public function getSalaryStatusAttribute()
    {
        if ($this->paid_amount >= $this->accumulated_salary) {
            return 'paid';
        } elseif ($this->accumulated_salary > 0) {
            return 'pending';
        }
        return 'none';
    }
}

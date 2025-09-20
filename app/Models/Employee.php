<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'position',
        'department',
        'salary',
        'hire_date',
        'is_active'
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    protected $dates = [
        'hire_date',
        'created_at',
        'updated_at'
    ];

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }

    public function getTotalPaidForMonth($monthYear)
    {
        return $this->salaryPayments()
            ->where('month_year', $monthYear)
            ->sum('amount');
    }

    public function getRemainingSalaryForMonth($monthYear)
    {
        $totalPaid = $this->getTotalPaidForMonth($monthYear);
        return max(0, ($this->salary ?? 0) - $totalPaid);
    }

    public function getTotalRemainingSalaryFromHireDate($includeCurrentMonth = true)
    {
        if (!$this->hire_date) {
            return $this->salary ?? 0;
        }

        $hireDate = \Carbon\Carbon::parse($this->hire_date);
        $currentDate = \Carbon\Carbon::now();
        
        if (!$includeCurrentMonth) {
            $currentDate = $currentDate->subMonth();
        }

        $totalMonths = $hireDate->diffInMonths($currentDate) + 1;
        $totalSalary = $totalMonths * ($this->salary ?? 0);
        
        // Get total paid from hire date
        $totalPaid = $this->salaryPayments()
            ->where('month_year', '>=', $hireDate->format('Y-m'))
            ->where('month_year', '<=', $currentDate->format('Y-m'))
            ->sum('amount');
            
        return max(0, $totalSalary - $totalPaid);
    }
}
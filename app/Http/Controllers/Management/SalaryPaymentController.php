<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryPayment;
use Illuminate\Http\Request;

class SalaryPaymentController extends Controller
{
    /**
     * Show the form for creating a new salary payment.
     */
    public function create(Employee $employee)
    {
        $currentMonth = now()->format('Y-m');
        $remainingSalary = $employee->getRemainingSalaryForMonth($currentMonth);
        
        return view('management.salary-payments.create', compact('employee', 'remainingSalary', 'currentMonth'));
    }

    /**
     * Store a newly created salary payment.
     */
    public function store(Request $request, Employee $employee)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,check',
            'notes' => 'nullable|string|max:500',
            'month_year' => 'required|string'
        ]);

        // Check if amount doesn't exceed remaining salary
        $remainingSalary = $employee->getRemainingSalaryForMonth($request->month_year);
        if ($request->amount > $remainingSalary) {
            return back()->withErrors(['amount' => 'Ödeme tutarı kalan maaştan fazla olamaz. Kalan maaş: ' . number_format($remainingSalary, 2) . ' ₺']);
        }

        $accountId = session('current_account_id');
        $userId = auth()->id();
        
        if (!$accountId) {
            $accountId = \App\Models\Account::active()->first()?->id ?? 1;
        }
        if (!$userId) {
            $userId = \App\Models\User::first()?->id ?? 1;
        }

        SalaryPayment::create([
            'employee_id' => $employee->id,
            'account_id' => $accountId,
            'user_id' => $userId,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
            'month_year' => $request->month_year
        ]);

        return redirect()->route('management.employees.index')
                        ->with('success', 'Maaş ödemesi başarıyla kaydedildi.');
    }

    /**
     * Display salary payments for an employee.
     */
    public function show(Employee $employee)
    {
        $payments = $employee->salaryPayments()
            ->with(['account', 'user'])
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        return view('management.salary-payments.show', compact('employee', 'payments'));
    }

    /**
     * Get remaining salary for an employee for a specific month.
     */
    public function getRemainingSalary(Employee $employee, Request $request)
    {
        $monthYear = $request->get('month', now()->format('Y-m'));
        $remaining = $employee->getRemainingSalaryForMonth($monthYear);
        
        return response()->json([
            'remaining' => $remaining,
            'month' => $monthYear
        ]);
    }

    /**
     * Get total remaining salary from hire date.
     */
    public function getTotalRemainingSalary(Employee $employee, Request $request)
    {
        $includeCurrentMonth = $request->get('include_current', true);
        $remaining = $employee->getTotalRemainingSalaryFromHireDate($includeCurrentMonth);
        
        return response()->json([
            'remaining' => $remaining,
            'hire_date' => $employee->hire_date,
            'include_current' => $includeCurrentMonth
        ]);
    }
}

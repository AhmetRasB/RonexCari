<?php

namespace App\Http\Controllers\Expenses;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::orderBy('name')->paginate(15);
        return view('expenses.employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('expenses.employees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            Log::info('Employee creation request received', ['request_data' => $request->all()]);

            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'monthly_salary' => 'required|numeric|min:0',
                'salary_day' => 'required|integer|min:1|max:28',
                'is_active' => 'boolean'
            ], [
                'name.required' => 'Çalışan adı gereklidir.',
                'name.string' => 'Çalışan adı metin olmalıdır.',
                'name.max' => 'Çalışan adı en fazla 255 karakter olabilir.',
                'phone.string' => 'Telefon numarası metin olmalıdır.',
                'phone.max' => 'Telefon numarası en fazla 20 karakter olabilir.',
                'emergency_contact_name.string' => 'Yakın adı metin olmalıdır.',
                'emergency_contact_name.max' => 'Yakın adı en fazla 255 karakter olabilir.',
                'emergency_contact_phone.string' => 'Yakın telefonu metin olmalıdır.',
                'emergency_contact_phone.max' => 'Yakın telefonu en fazla 20 karakter olabilir.',
                'monthly_salary.required' => 'Aylık maaş gereklidir.',
                'monthly_salary.numeric' => 'Aylık maaş sayısal olmalıdır.',
                'monthly_salary.min' => 'Aylık maaş 0\'dan büyük olmalıdır.',
                'salary_day.required' => 'Maaş günü gereklidir.',
                'salary_day.integer' => 'Maaş günü tam sayı olmalıdır.',
                'salary_day.min' => 'Maaş günü 1\'den küçük olamaz.',
                'salary_day.max' => 'Maaş günü 28\'den büyük olamaz.'
            ]);

            $data = $request->all();
            $data['is_active'] = $request->has('is_active');
            $data['accumulated_salary'] = 0;
            $data['paid_amount'] = 0;

            $employee = Employee::create($data);

            Log::info('Employee created successfully', ['employee_id' => $employee->id]);

            return redirect()->route('expenses.employees.index')
                ->with('success', 'Çalışan başarıyla oluşturuldu.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Employee creation validation failed', ['errors' => $e->errors()]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Employee creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->with('error', 'Çalışan oluşturulurken bir hata oluştu.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        return view('expenses.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        return view('expenses.employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        try {
            Log::info('Employee update request received', ['employee_id' => $employee->id, 'request_data' => $request->all()]);

            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'monthly_salary' => 'required|numeric|min:0',
                'salary_day' => 'required|integer|min:1|max:28',
                'is_active' => 'boolean'
            ], [
                'name.required' => 'Çalışan adı gereklidir.',
                'name.string' => 'Çalışan adı metin olmalıdır.',
                'name.max' => 'Çalışan adı en fazla 255 karakter olabilir.',
                'phone.string' => 'Telefon numarası metin olmalıdır.',
                'phone.max' => 'Telefon numarası en fazla 20 karakter olabilir.',
                'emergency_contact_name.string' => 'Yakın adı metin olmalıdır.',
                'emergency_contact_name.max' => 'Yakın adı en fazla 255 karakter olabilir.',
                'emergency_contact_phone.string' => 'Yakın telefonu metin olmalıdır.',
                'emergency_contact_phone.max' => 'Yakın telefonu en fazla 20 karakter olabilir.',
                'monthly_salary.required' => 'Aylık maaş gereklidir.',
                'monthly_salary.numeric' => 'Aylık maaş sayısal olmalıdır.',
                'monthly_salary.min' => 'Aylık maaş 0\'dan büyük olmalıdır.',
                'salary_day.required' => 'Maaş günü gereklidir.',
                'salary_day.integer' => 'Maaş günü tam sayı olmalıdır.',
                'salary_day.min' => 'Maaş günü 1\'den küçük olamaz.',
                'salary_day.max' => 'Maaş günü 28\'den büyük olamaz.'
            ]);

            $data = $request->all();
            $data['is_active'] = $request->has('is_active');

            $employee->update($data);

            Log::info('Employee updated successfully', ['employee_id' => $employee->id]);

            return redirect()->route('expenses.employees.index')
                ->with('success', 'Çalışan başarıyla güncellendi.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Employee update validation failed', ['employee_id' => $employee->id, 'errors' => $e->errors()]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Employee update failed', ['employee_id' => $employee->id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->with('error', 'Çalışan güncellenirken bir hata oluştu.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        try {
            Log::info('Employee deletion request received', ['employee_id' => $employee->id]);

            $employee->delete();

            Log::info('Employee deleted successfully', ['employee_id' => $employee->id]);

            return redirect()->route('expenses.employees.index')
                ->with('success', 'Çalışan başarıyla silindi.');

        } catch (\Exception $e) {
            Log::error('Employee deletion failed', ['employee_id' => $employee->id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->with('error', 'Çalışan silinirken bir hata oluştu.');
        }
    }

    /**
     * Pay salary to employee
     */
    public function paySalary(Request $request, Employee $employee)
    {
        try {
            Log::info('Salary payment request received', ['employee_id' => $employee->id, 'request_data' => $request->all()]);

            $request->validate([
                'amount' => 'required|numeric|min:0|max:' . $employee->remaining_salary
            ], [
                'amount.required' => 'Ödeme tutarı gereklidir.',
                'amount.numeric' => 'Ödeme tutarı sayısal olmalıdır.',
                'amount.min' => 'Ödeme tutarı 0\'dan büyük olmalıdır.',
                'amount.max' => 'Ödeme tutarı kalan borçtan fazla olamaz.'
            ]);

            $amount = (float) $request->amount;
            $employee->increment('paid_amount', $amount);
            $employee->update(['last_payment_date' => now()]);

            Log::info('Salary payment successful', ['employee_id' => $employee->id, 'amount' => $amount]);

            return redirect()->back()
                ->with('success', 'Maaş ödemesi başarıyla yapıldı.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Salary payment validation failed', ['employee_id' => $employee->id, 'errors' => $e->errors()]);
            return redirect()->back()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Salary payment failed', ['employee_id' => $employee->id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->with('error', 'Maaş ödemesi yapılırken bir hata oluştu.');
        }
    }
}

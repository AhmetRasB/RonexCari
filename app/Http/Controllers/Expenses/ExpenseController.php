<?php

namespace App\Http\Controllers\Expenses;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenses = Expense::orderBy('expense_date', 'desc')->paginate(15);
        return view('expenses.expenses.index', compact('expenses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('expenses.expenses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            Log::info('Expense creation request received', ['request_data' => $request->all()]);

            $request->validate([
                'name' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'expense_date' => 'required|date',
                'is_active' => 'boolean'
            ], [
                'name.required' => 'Gider adı gereklidir.',
                'name.string' => 'Gider adı metin olmalıdır.',
                'name.max' => 'Gider adı en fazla 255 karakter olabilir.',
                'amount.required' => 'Tutar gereklidir.',
                'amount.numeric' => 'Tutar sayısal olmalıdır.',
                'amount.min' => 'Tutar 0\'dan büyük olmalıdır.',
                'expense_date.required' => 'Gider tarihi gereklidir.',
                'expense_date.date' => 'Geçerli bir tarih giriniz.'
            ]);

            $data = $request->all();
            $data['is_active'] = $request->has('is_active');

            $expense = Expense::create($data);

            Log::info('Expense created successfully', ['expense_id' => $expense->id]);

            return redirect()->route('expenses.expenses.index')
                ->with('success', 'Gider başarıyla oluşturuldu.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Expense creation validation failed', ['errors' => $e->errors()]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Expense creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->with('error', 'Gider oluşturulurken bir hata oluştu.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        return view('expenses.expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        return view('expenses.expenses.edit', compact('expense'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        try {
            Log::info('Expense update request received', ['expense_id' => $expense->id, 'request_data' => $request->all()]);

            $request->validate([
                'name' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'expense_date' => 'required|date',
                'is_active' => 'boolean'
            ], [
                'name.required' => 'Gider adı gereklidir.',
                'name.string' => 'Gider adı metin olmalıdır.',
                'name.max' => 'Gider adı en fazla 255 karakter olabilir.',
                'amount.required' => 'Tutar gereklidir.',
                'amount.numeric' => 'Tutar sayısal olmalıdır.',
                'amount.min' => 'Tutar 0\'dan büyük olmalıdır.',
                'expense_date.required' => 'Gider tarihi gereklidir.',
                'expense_date.date' => 'Geçerli bir tarih giriniz.'
            ]);

            $data = $request->all();
            $data['is_active'] = $request->has('is_active');

            $expense->update($data);

            Log::info('Expense updated successfully', ['expense_id' => $expense->id]);

            return redirect()->route('expenses.expenses.index')
                ->with('success', 'Gider başarıyla güncellendi.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Expense update validation failed', ['expense_id' => $expense->id, 'errors' => $e->errors()]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Expense update failed', ['expense_id' => $expense->id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->with('error', 'Gider güncellenirken bir hata oluştu.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        try {
            Log::info('Expense deletion request received', ['expense_id' => $expense->id]);

            $expense->delete();

            Log::info('Expense deleted successfully', ['expense_id' => $expense->id]);

            return redirect()->route('expenses.expenses.index')
                ->with('success', 'Gider başarıyla silindi.');

        } catch (\Exception $e) {
            Log::error('Expense deletion failed', ['expense_id' => $expense->id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->with('error', 'Gider silinirken bir hata oluştu.');
        }
    }
}

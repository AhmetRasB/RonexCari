<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\SupplierPayment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SupplierPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $supplierPayments = SupplierPayment::with('supplier')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('finance.supplier-payments.index', compact('supplierPayments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $selectedSupplierId = $request->get('supplier_id');
        return view('finance.supplier-payments.create', compact('suppliers', 'selectedSupplierId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Supplier payment store method called', [
            'request_data' => $request->all(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'timestamp' => now()
        ]);
        
        try {
            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'payment_type' => 'required|in:nakit,banka,kredi_karti,havale,eft',
                'transaction_date' => 'required|date',
                'amount' => 'required|numeric|min:0.01',
                'currency' => 'required|in:TRY,USD,EUR',
                'description' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            $validated['account_id'] = session('selected_account_id', 1); // Default account ID
            $supplierPayment = SupplierPayment::create($validated);

            // Update supplier payment amounts
            $supplier = Supplier::find($validated['supplier_id']);
            Log::info('Supplier lookup', ['supplier_id' => $validated['supplier_id'], 'supplier_found' => $supplier ? 'yes' : 'no']);
            
            if ($supplier) {
                $currencyField = 'balance_' . strtolower($validated['currency']);
                $paidField = 'paid_amount_' . strtolower($validated['currency']);
                Log::info('Currency field check', ['currency_field' => $currencyField, 'paid_field' => $paidField]);
                
                if (in_array($currencyField, ['balance_try', 'balance_usd', 'balance_eur']) && 
                    in_array($paidField, ['paid_amount_try', 'paid_amount_usd', 'paid_amount_eur'])) {
                    
                    $currentBalance = $supplier->$currencyField;
                    $currentPaid = $supplier->$paidField;
                    
                    Log::info('Supplier payment balance update', [
                        'supplier_id' => $supplier->id,
                        'supplier_name' => $supplier->name,
                        'currency_field' => $currencyField,
                        'paid_field' => $paidField,
                        'current_balance' => $currentBalance,
                        'current_paid' => $currentPaid,
                        'payment_amount' => $validated['amount']
                    ]);
                    
                    // Check if payment amount is valid
                    $remainingBalance = $currentBalance - $currentPaid;
                    if ($validated['amount'] > $remainingBalance) {
                        DB::rollBack();
                        return back()->withInput()
                            ->with('error', 'Ödeme tutarı kalan borçtan fazla olamaz. Kalan borç: ' . number_format($remainingBalance, 2) . ' ' . $validated['currency']);
                    }
                    
                    // Update paid amount
                    $supplier->$paidField = $currentPaid + $validated['amount'];
                    $supplier->last_payment_date = now();
                    $supplier->save();
                    
                    Log::info('Supplier payment made, balance updated', [
                        'old_paid' => $currentPaid,
                        'payment_amount' => $validated['amount'],
                        'new_paid' => $supplier->$paidField
                    ]);
                }
            }

            DB::commit();

            Log::info('Supplier payment created successfully', ['supplier_payment_id' => $supplierPayment->id]);

            return redirect()->route('finance.supplier-payments.index')
                ->with('success', 'Tedarikçi ödemesi başarıyla kaydedildi.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Supplier payment creation failed', ['error' => $e->getMessage()]);
            
            return back()->withInput()
                ->with('error', 'Tedarikçi ödemesi kaydedilirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SupplierPayment $supplierPayment)
    {
        return view('finance.supplier-payments.show', compact('supplierPayment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SupplierPayment $supplierPayment)
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        return view('finance.supplier-payments.edit', compact('supplierPayment', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SupplierPayment $supplierPayment)
    {
        try {
            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'payment_type' => 'required|in:nakit,banka,kredi_karti,havale,eft',
                'transaction_date' => 'required|date',
                'amount' => 'required|numeric|min:0.01',
                'currency' => 'required|in:TRY,USD,EUR',
                'description' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            // Revert old payment effect
            $oldSupplier = $supplierPayment->supplier;
            if ($oldSupplier) {
                $oldCurrencyField = 'balance_' . strtolower($supplierPayment->currency);
                $oldPaidField = 'paid_amount_' . strtolower($supplierPayment->currency);
                if (in_array($oldCurrencyField, ['balance_try', 'balance_usd', 'balance_eur']) && 
                    in_array($oldPaidField, ['paid_amount_try', 'paid_amount_usd', 'paid_amount_eur'])) {
                    // Revert the payment effect
                    $oldSupplier->$oldPaidField -= $supplierPayment->amount;
                    $oldSupplier->save();
                }
            }

            $supplierPayment->update($validated);

            // Apply new payment effect
            $newSupplier = Supplier::find($validated['supplier_id']);
            if ($newSupplier) {
                $newCurrencyField = 'balance_' . strtolower($validated['currency']);
                $newPaidField = 'paid_amount_' . strtolower($validated['currency']);
                if (in_array($newCurrencyField, ['balance_try', 'balance_usd', 'balance_eur']) && 
                    in_array($newPaidField, ['paid_amount_try', 'paid_amount_usd', 'paid_amount_eur'])) {
                    
                    // Check if new payment amount is valid
                    $remainingBalance = $newSupplier->$newCurrencyField - $newSupplier->$newPaidField;
                    if ($validated['amount'] > $remainingBalance) {
                        DB::rollBack();
                        return back()->withInput()
                            ->with('error', 'Ödeme tutarı kalan borçtan fazla olamaz. Kalan borç: ' . number_format($remainingBalance, 2) . ' ' . $validated['currency']);
                    }
                    
                    $newSupplier->$newPaidField += $validated['amount'];
                    $newSupplier->last_payment_date = now();
                    $newSupplier->save();
                }
            }

            DB::commit();

            Log::info('Supplier payment updated successfully', ['supplier_payment_id' => $supplierPayment->id]);

            return redirect()->route('finance.supplier-payments.index')
                ->with('success', 'Tedarikçi ödemesi başarıyla güncellendi.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Supplier payment update failed', ['error' => $e->getMessage()]);
            
            return back()->withInput()
                ->with('error', 'Tedarikçi ödemesi güncellenirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SupplierPayment $supplierPayment)
    {
        try {
            DB::beginTransaction();

            // Revert payment effect on supplier
            $supplier = $supplierPayment->supplier;
            if ($supplier) {
                $currencyField = 'balance_' . strtolower($supplierPayment->currency);
                $paidField = 'paid_amount_' . strtolower($supplierPayment->currency);
                if (in_array($currencyField, ['balance_try', 'balance_usd', 'balance_eur']) && 
                    in_array($paidField, ['paid_amount_try', 'paid_amount_usd', 'paid_amount_eur'])) {
                    // Revert the payment effect
                    $supplier->$paidField -= $supplierPayment->amount;
                    $supplier->save();
                }
            }

            $supplierPayment->delete();

            DB::commit();

            Log::info('Supplier payment deleted successfully', ['supplier_payment_id' => $supplierPayment->id]);

            return redirect()->route('finance.supplier-payments.index')
                ->with('success', 'Tedarikçi ödemesi başarıyla silindi.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Supplier payment deletion failed', ['error' => $e->getMessage()]);
            
            return back()
                ->with('error', 'Tedarikçi ödemesi silinirken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Search suppliers for AJAX requests
     */
    public function searchSuppliers(Request $request)
    {
        $query = $request->get('q');
        $suppliers = Supplier::where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('company_name', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'company_name']);

        return response()->json($suppliers);
    }

    /**
     * Print supplier payment
     */
    public function print(SupplierPayment $supplierPayment)
    {
        // Load supplier relationship
        $supplierPayment->load('supplier');
        
        // Convert amount to words (with currency-aware units)
        $amountInWords = $this->numberToWords($supplierPayment->amount, $supplierPayment->currency);
        
        // Remaining supplier balances in all currencies
        $remainingBalances = null;
        if ($supplierPayment->supplier) {
            $remainingBalances = [
                'TRY' => (float) (($supplierPayment->supplier->balance_try ?? 0) - ($supplierPayment->supplier->paid_amount_try ?? 0)),
                'USD' => (float) (($supplierPayment->supplier->balance_usd ?? 0) - ($supplierPayment->supplier->paid_amount_usd ?? 0)),
                'EUR' => (float) (($supplierPayment->supplier->balance_eur ?? 0) - ($supplierPayment->supplier->paid_amount_eur ?? 0)),
            ];
        }
        
        return view('finance.supplier-payments.print', compact('supplierPayment', 'amountInWords', 'remainingBalances'));
    }

    /**
     * Convert number to Turkish words
     */
    private function numberToWords($number, $currency = 'TRY')
    {
        $ones = [
            '', 'bir', 'iki', 'üç', 'dört', 'beş', 'altı', 'yedi', 'sekiz', 'dokuz',
            'on', 'on bir', 'on iki', 'on üç', 'on dört', 'on beş', 'on altı', 'on yedi', 'on sekiz', 'on dokuz'
        ];
        
        $tens = [
            '', '', 'yirmi', 'otuz', 'kırk', 'elli', 'altmış', 'yetmiş', 'seksen', 'doksan'
        ];
        
        $groups = [
            '', 'bin', 'milyon', 'milyar'
        ];

        if ($number == 0) {
            return 'sıfır';
        }

        $number = number_format($number, 2, '.', '');
        $parts = explode('.', $number);
        $integerPart = (int)$parts[0];
        $decimalPart = isset($parts[1]) ? (int)$parts[1] : 0;

        $result = $this->convertIntegerToWords($integerPart, $ones, $tens, $groups);
        
        // Currency-aware unit labels
        $currency = strtoupper($currency ?? 'TRY');
        $unitMain = 'lira';
        $unitSub = 'kuruş';
        if ($currency === 'USD') { $unitMain = 'dolar'; $unitSub = 'sent'; }
        elseif ($currency === 'EUR') { $unitMain = 'euro'; $unitSub = 'sent'; }

        if ($decimalPart > 0) {
            $result .= ' ' . $unitMain . ' ' . $this->convertIntegerToWords($decimalPart, $ones, $tens, $groups) . ' ' . $unitSub;
        } else {
            $result .= ' ' . $unitMain;
        }

        return ucfirst(trim($result));
    }

    private function convertIntegerToWords($number, $ones, $tens, $groups)
    {
        if ($number == 0) {
            return '';
        }

        $result = '';
        $groupIndex = 0;

        while ($number > 0) {
            $group = $number % 1000;
            if ($group != 0) {
                $groupWords = $this->convertHundredsToWords($group, $ones, $tens);
                if ($groupIndex > 0) {
                    $groupWords .= ' ' . $groups[$groupIndex];
                }
                $result = $groupWords . ' ' . $result;
            }
            $number = intval($number / 1000);
            $groupIndex++;
        }

        return trim($result);
    }

    private function convertHundredsToWords($number, $ones, $tens)
    {
        $result = '';

        $hundreds = intval($number / 100);
        $remainder = $number % 100;

        if ($hundreds > 0) {
            if ($hundreds == 1) {
                $result .= 'yüz';
            } else {
                $result .= $ones[$hundreds] . ' yüz';
            }
        }

        if ($remainder > 0) {
            if ($remainder < 20) {
                $result .= ' ' . $ones[$remainder];
            } else {
                $tensDigit = intval($remainder / 10);
                $onesDigit = $remainder % 10;
                $result .= ' ' . $tens[$tensDigit];
                if ($onesDigit > 0) {
                    $result .= ' ' . $ones[$onesDigit];
                }
            }
        }

        return trim($result);
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = json_decode($request->input('ids'), true);
            if (empty($ids) || !is_array($ids)) {
                return redirect()->back()->with('error', 'Geçersiz seçim');
            }
            $deletedCount = \App\Models\SupplierPayment::whereIn('id', $ids)->delete();
            return redirect()->route('finance.supplier-payments.index')->with('success', $deletedCount . ' tedarikçi ödemesi başarıyla silindi');
        } catch (\Exception $e) {
            \Log::error('Bulk delete error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Silme işlemi sırasında bir hata oluştu');
        }
    }
}

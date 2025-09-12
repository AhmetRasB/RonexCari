<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::latest()->paginate(15);
        return view('purchases.suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('purchases.suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Supplier store method called', [
            'request_data' => $request->all(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'timestamp' => now(),
            'is_ajax' => $request->ajax(),
            'content_type' => $request->header('Content-Type')
        ]);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'email' => 'required|email|unique:suppliers,email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:1000',
                'tax_number' => 'nullable|string|max:50',
                'contact_person' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:2000',
                'is_active' => 'boolean'
            ], [
                'name.required' => 'Tedarikçi adı zorunludur.',
                'name.max' => 'Tedarikçi adı çok uzun.',
                'company_name.max' => 'Şirket adı çok uzun.',
                'email.required' => 'E-posta adresi zorunludur.',
                'email.email' => 'Geçerli bir e-posta adresi girin.',
                'email.unique' => 'Bu e-posta adresi zaten kullanılıyor.',
                'email.max' => 'E-posta adresi çok uzun.',
                'phone.max' => 'Telefon numarası çok uzun.',
                'address.max' => 'Adres çok uzun.',
                'tax_number.max' => 'Vergi numarası çok uzun.',
                'contact_person.max' => 'İletişim kişisi adı çok uzun.',
                'notes.max' => 'Notlar çok uzun.',
            ]);
            
            \Log::info('Supplier validation passed', ['validated_data' => $validated]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Supplier validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        try {
            $supplier = Supplier::create($validated);

            \Log::info('Supplier created successfully', [
                'supplier_id' => $supplier->id,
                'name' => $supplier->name,
                'company_name' => $supplier->company_name,
                'email' => $supplier->email,
                'phone' => $supplier->phone
            ]);

            // If it's an AJAX request, return JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tedarikçi başarıyla oluşturuldu.',
                    'supplier' => $supplier
                ]);
            }

            return redirect()->route('purchases.suppliers.index')
                ->with('success', 'Tedarikçi başarıyla oluşturuldu.');
                
        } catch (\Exception $e) {
            \Log::error('Supplier creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'validated_data' => $validated ?? null
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tedarikçi oluşturulurken bir hata oluştu: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()
                ->with('error', 'Tedarikçi oluşturulurken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        return view('purchases.suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        return view('purchases.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:suppliers,email,' . $supplier->id . '|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'tax_number' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'is_active' => 'boolean'
        ], [
            'name.required' => 'Tedarikçi adı zorunludur.',
            'name.max' => 'Tedarikçi adı çok uzun.',
            'company_name.max' => 'Şirket adı çok uzun.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.unique' => 'Bu e-posta adresi zaten kullanılıyor.',
            'email.max' => 'E-posta adresi çok uzun.',
            'phone.max' => 'Telefon numarası çok uzun.',
            'address.max' => 'Adres çok uzun.',
            'tax_number.max' => 'Vergi numarası çok uzun.',
            'contact_person.max' => 'İletişim kişisi adı çok uzun.',
            'notes.max' => 'Notlar çok uzun.',
        ]);

        $supplier->update($validated);

        return redirect()->route('purchases.suppliers.index')
            ->with('success', 'Tedarikçi başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('purchases.suppliers.index')
            ->with('success', 'Tedarikçi başarıyla silindi.');
    }

    /**
     * Make payment to supplier
     */
    public function makePayment(Request $request, Supplier $supplier)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|in:TRY,USD,EUR',
            'description' => 'nullable|string|max:1000',
        ], [
            'amount.required' => 'Ödeme tutarı zorunludur.',
            'amount.numeric' => 'Ödeme tutarı sayısal olmalıdır.',
            'amount.min' => 'Ödeme tutarı en az 0.01 olmalıdır.',
            'currency.required' => 'Para birimi zorunludur.',
            'currency.in' => 'Geçerli bir para birimi seçin.',
            'description.max' => 'Açıklama çok uzun.',
        ]);

        try {
            // Check if payment amount is valid
            $currencyField = 'balance_' . strtolower($request->currency);
            $paidField = 'paid_amount_' . strtolower($request->currency);
            $remainingBalance = $supplier->$currencyField - $supplier->$paidField;

            if ($request->amount > $remainingBalance) {
                return redirect()->back()
                    ->with('error', 'Ödeme tutarı kalan borçtan fazla olamaz. Kalan borç: ' . number_format($remainingBalance, 2) . ' ' . $request->currency);
            }

            // Update supplier payment amounts
            $supplier->$paidField += $request->amount;
            $supplier->last_payment_date = now();
            $supplier->save();

            \Log::info('Supplier payment made successfully', [
                'supplier_id' => $supplier->id,
                'amount' => $request->amount,
                'currency' => $request->currency,
                'description' => $request->description,
                'remaining_balance' => $remainingBalance - $request->amount
            ]);

            $message = 'Ödeme başarıyla yapıldı.';
            if ($supplier->payment_status === 'paid') {
                $message .= ' Tüm borçlar ödendi!';
            }

            return redirect()->back()
                ->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Supplier payment failed', [
                'error' => $e->getMessage(),
                'supplier_id' => $supplier->id,
                'request_data' => $request->all()
            ]);

            return redirect()->back()
                ->with('error', 'Ödeme yapılırken bir hata oluştu: ' . $e->getMessage());
        }
    }
}

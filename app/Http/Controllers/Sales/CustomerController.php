<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::latest()->paginate(15);
        return view('sales.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sales.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Customer store method called', [
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
                'email' => 'nullable|email|unique:customers,email|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'nullable|string|max:1000',
                'tax_number' => 'nullable|string|max:50',
                'contact_person' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:2000',
                'is_active' => 'boolean'
            ], [
                'name.required' => 'Müşteri adı zorunludur.',
                'name.max' => 'Müşteri adı çok uzun.',
                'company_name.max' => 'Şirket adı çok uzun.',
                'email.email' => 'Geçerli bir e-posta adresi girin.',
                'email.unique' => 'Bu e-posta adresi zaten kullanılıyor.',
                'email.max' => 'E-posta adresi çok uzun.',
                'phone.required' => 'Telefon numarası zorunludur.',
                'phone.max' => 'Telefon numarası çok uzun.',
                'address.max' => 'Adres çok uzun.',
                'tax_number.max' => 'Vergi numarası çok uzun.',
                'contact_person.max' => 'İletişim kişisi adı çok uzun.',
                'notes.max' => 'Notlar çok uzun.',
            ]);
            
            \Log::info('Customer validation passed', ['validated_data' => $validated]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Customer validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Müşteri oluşturulurken validasyon hatası oluştu.',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Müşteri oluşturulurken validasyon hatası oluştu. Lütfen tüm zorunlu alanları doldurun.');
        }

        try {
            $customer = Customer::create($validated);

            \Log::info('Customer created successfully', [
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'company_name' => $customer->company_name,
                'email' => $customer->email,
                'phone' => $customer->phone
            ]);

            // If it's an AJAX request, return JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Müşteri başarıyla oluşturuldu.',
                    'customer' => $customer
                ]);
            }

            return redirect()->route('sales.customers.index')
                ->with('success', 'Müşteri başarıyla oluşturuldu.');
                
        } catch (\Exception $e) {
            \Log::error('Customer creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'validated_data' => $validated ?? null
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Müşteri oluşturulurken bir hata oluştu: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()
                ->with('error', 'Müşteri oluşturulurken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return view('sales.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('sales.customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'email' => ['nullable', 'email', Rule::unique('customers')->ignore($customer->id)],
                'phone' => 'required|string|max:20',
                'address' => 'nullable|string|max:500',
                'tax_number' => 'nullable|string|max:50',
                'contact_person' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'is_active' => 'boolean'
            ], [
                'name.required' => 'Müşteri adı zorunludur.',
                'name.max' => 'Müşteri adı çok uzun.',
                'company_name.max' => 'Şirket adı çok uzun.',
                'email.email' => 'Geçerli bir e-posta adresi girin.',
                'email.unique' => 'Bu e-posta adresi zaten kullanılıyor.',
                'phone.required' => 'Telefon numarası zorunludur.',
                'phone.max' => 'Telefon numarası çok uzun.',
                'address.max' => 'Adres çok uzun.',
                'tax_number.max' => 'Vergi numarası çok uzun.',
                'contact_person.max' => 'İletişim kişisi adı çok uzun.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Müşteri güncellenirken validasyon hatası oluştu. Lütfen tüm zorunlu alanları doldurun.');
        }

        $customer->update($validated);

        return redirect()->route('sales.customers.index')
            ->with('success', 'Müşteri başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('sales.customers.index')
            ->with('success', 'Müşteri başarıyla silindi.');
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = json_decode($request->input('ids'), true);
            if (empty($ids) || !is_array($ids)) {
                return redirect()->back()->with('error', 'Geçersiz seçim');
            }
            $deletedCount = \App\Models\Customer::whereIn('id', $ids)->delete();
            return redirect()->route('sales.customers.index')->with('success', $deletedCount . ' müşteri başarıyla silindi');
        } catch (\Exception $e) {
            \Log::error('Bulk delete error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Silme işlemi sırasında bir hata oluştu');
        }
    }
}

<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::orderBy('created_at', 'desc')->paginate(15);
        return view('products.services.index', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.services.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Service store method called', [
            'request_data' => $request->all(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'timestamp' => now()
        ]);
        
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'service_code' => 'nullable|string|max:255|unique:services,code',
                'category' => 'nullable|string|max:255',
                'sale_price' => 'nullable|numeric|min:0|max:999999.99',
                'currency' => 'nullable|string|in:TRY,USD,EUR',
                'vat_rate' => 'nullable|numeric|min:0|max:100',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'boolean',
            ], [
                'name.required' => 'Hizmet adı zorunludur.',
                'name.max' => 'Hizmet adı çok uzun.',
                'service_code.unique' => 'Bu hizmet kodu zaten kullanılıyor.',
                'service_code.max' => 'Hizmet kodu çok uzun.',
                'category.max' => 'Kategori çok uzun.',
                'sale_price.numeric' => 'Satış fiyatı sayısal olmalıdır.',
                'sale_price.min' => 'Satış fiyatı 0\'dan küçük olamaz.',
                'sale_price.max' => 'Satış fiyatı çok büyük.',
                'currency.in' => 'Geçersiz para birimi.',
                'vat_rate.numeric' => 'KDV oranı sayısal olmalıdır.',
                'vat_rate.min' => 'KDV oranı 0\'dan küçük olamaz.',
                'vat_rate.max' => 'KDV oranı %100\'den büyük olamaz.',
                'description.max' => 'Açıklama çok uzun.',
            ]);
            
            \Log::info('Service validation passed', ['validated_data' => $validated]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Service validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Hizmet oluşturulurken validasyon hatası oluştu. Lütfen tüm zorunlu alanları doldurun.');
        }

        try {
            $data = $validated;
            $data['price'] = $data['sale_price']; // sale_price'ı price olarak kaydet
            $data['code'] = $data['service_code'] ?? 'SRV-' . time(); // service_code'u code olarak kaydet
            unset($data['sale_price']); // sale_price'ı kaldır
            
            $service = Service::create($data);
            
            \Log::info('Service created successfully', [
                'service_id' => $service->id,
                'code' => $service->code,
                'name' => $service->name,
                'category' => $service->category,
                'price' => $service->price,
                'currency' => $service->currency
            ]);

            return redirect()->route('services.index')
                ->with('success', 'Hizmet başarıyla oluşturuldu.');
                
        } catch (\Exception $e) {
            \Log::error('Service creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'validated_data' => $validated ?? null
            ]);
            
            return back()->withInput()
                ->with('error', 'Hizmet oluşturulurken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        return view('products.services.show', compact('service'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        return view('products.services.edit', compact('service'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'service_code' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'sale_price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'vat_rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['price'] = $data['sale_price']; // sale_price'ı price olarak kaydet
        $data['code'] = $data['service_code'] ?? 'SRV-' . time(); // service_code'u code olarak kaydet
        unset($data['sale_price']); // sale_price'ı kaldır
        
        $service->update($data);

        return redirect()->route('services.index')
            ->with('success', 'Hizmet başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Hizmet başarıyla silindi.');
    }
}

<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::orderBy('created_at', 'desc')->get();
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Product store method called', [
            'request_data' => $request->all(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'timestamp' => now()
        ]);
        
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'product_code' => 'nullable|string|max:255|unique:products',
                'unit' => 'nullable|string|max:50',
                'sale_price' => 'nullable|numeric|min:0|max:999999.99',
                'purchase_price' => 'nullable|numeric|min:0|max:999999.99',
                'currency' => 'nullable|string|in:TRY,USD,EUR',
                'vat_rate' => 'nullable|numeric|min:0|max:100',
                'category' => 'nullable|string|max:255',
                'brand' => 'nullable|string|max:255',
                'size' => 'nullable|string|max:50',
                'color' => 'nullable|string|max:50',
                'barcode' => 'nullable|string|max:255|unique:products',
                'supplier_code' => 'nullable|string|max:255',
                'gtip_code' => 'nullable|string|max:255',
                'class_code' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'initial_stock' => 'nullable|integer|min:0|max:999999',
                'critical_stock' => 'nullable|integer|min:0|max:999999',
                'is_active' => 'boolean',
            ], [
                'name.required' => 'Ürün adı zorunludur.',
                'name.max' => 'Ürün adı çok uzun.',
                'product_code.unique' => 'Bu ürün kodu zaten kullanılıyor.',
                'product_code.max' => 'Ürün kodu çok uzun.',
                'unit.max' => 'Birim çok uzun.',
                'sale_price.numeric' => 'Satış fiyatı sayısal olmalıdır.',
                'sale_price.min' => 'Satış fiyatı 0\'dan küçük olamaz.',
                'sale_price.max' => 'Satış fiyatı çok büyük.',
                'purchase_price.numeric' => 'Alış fiyatı sayısal olmalıdır.',
                'purchase_price.min' => 'Alış fiyatı 0\'dan küçük olamaz.',
                'purchase_price.max' => 'Alış fiyatı çok büyük.',
                'currency.in' => 'Geçersiz para birimi.',
                'vat_rate.numeric' => 'KDV oranı sayısal olmalıdır.',
                'vat_rate.min' => 'KDV oranı 0\'dan küçük olamaz.',
                'vat_rate.max' => 'KDV oranı %100\'den büyük olamaz.',
                'category.max' => 'Kategori çok uzun.',
                'brand.max' => 'Marka çok uzun.',
                'size.max' => 'Beden çok uzun.',
                'color.max' => 'Renk çok uzun.',
                'barcode.unique' => 'Bu barkod zaten kullanılıyor.',
                'barcode.max' => 'Barkod çok uzun.',
                'supplier_code.max' => 'Tedarikçi kodu çok uzun.',
                'gtip_code.max' => 'GTIP kodu çok uzun.',
                'class_code.max' => 'Sınıf kodu çok uzun.',
                'description.max' => 'Açıklama çok uzun.',
                'image.image' => 'Görsel dosyası geçerli bir resim olmalıdır.',
                'image.mimes' => 'Görsel dosyası jpeg, png, jpg, gif veya webp formatında olmalıdır.',
                'image.max' => 'Görsel dosyası 2MB\'dan büyük olamaz.',
                'initial_stock.integer' => 'Başlangıç stok tam sayı olmalıdır.',
                'initial_stock.min' => 'Başlangıç stok 0\'dan küçük olamaz.',
                'initial_stock.max' => 'Başlangıç stok çok büyük.',
                'critical_stock.integer' => 'Kritik stok tam sayı olmalıdır.',
                'critical_stock.min' => 'Kritik stok 0\'dan küçük olamaz.',
                'critical_stock.max' => 'Kritik stok çok büyük.',
            ]);
            
            \Log::info('Product validation passed', ['validated_data' => $validated]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Product validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/products'), $imageName);
                $validated['image'] = 'uploads/products/' . $imageName;
            }

            $product = Product::create($validated);
            
            \Log::info('Product created successfully', [
                'product_id' => $product->id,
                'product_code' => $product->product_code,
                'name' => $product->name,
                'category' => $product->category,
                'brand' => $product->brand
            ]);

            return redirect()->route('products.index')
                ->with('success', 'Ürün başarıyla oluşturuldu.');
                
        } catch (\Exception $e) {
            \Log::error('Product creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'validated_data' => $validated ?? null
            ]);
            
            return back()->withInput()
                ->with('error', 'Ürün oluşturulurken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'product_code' => 'nullable|string|max:255|unique:products,product_code,' . $product->id,
            'unit' => 'nullable|string|max:50',
            'sale_price' => 'nullable|numeric|min:0|max:999999.99',
            'purchase_price' => 'nullable|numeric|min:0|max:999999.99',
            'currency' => 'nullable|string|in:TRY,USD,EUR',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'category' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:255',
            'supplier_code' => 'nullable|string|max:255',
            'gtip_code' => 'nullable|string|max:255',
            'class_code' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'initial_stock' => 'nullable|integer|min:0|max:999999',
            'critical_stock' => 'nullable|integer|min:0|max:999999',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Ürün adı zorunludur.',
            'name.max' => 'Ürün adı çok uzun.',
            'product_code.unique' => 'Bu ürün kodu zaten kullanılıyor.',
            'product_code.max' => 'Ürün kodu çok uzun.',
            'unit.max' => 'Birim çok uzun.',
            'sale_price.numeric' => 'Satış fiyatı sayısal olmalıdır.',
            'sale_price.min' => 'Satış fiyatı 0\'dan küçük olamaz.',
            'sale_price.max' => 'Satış fiyatı çok büyük.',
            'purchase_price.numeric' => 'Alış fiyatı sayısal olmalıdır.',
            'purchase_price.min' => 'Alış fiyatı 0\'dan küçük olamaz.',
            'purchase_price.max' => 'Alış fiyatı çok büyük.',
            'currency.in' => 'Geçersiz para birimi.',
            'vat_rate.numeric' => 'KDV oranı sayısal olmalıdır.',
            'vat_rate.min' => 'KDV oranı 0\'dan küçük olamaz.',
            'vat_rate.max' => 'KDV oranı %100\'den büyük olamaz.',
            'category.max' => 'Kategori çok uzun.',
            'brand.max' => 'Marka çok uzun.',
            'size.max' => 'Beden çok uzun.',
            'color.max' => 'Renk çok uzun.',
            'barcode.max' => 'Barkod çok uzun.',
            'supplier_code.max' => 'Tedarikçi kodu çok uzun.',
            'gtip_code.max' => 'GTIP kodu çok uzun.',
            'class_code.max' => 'Sınıf kodu çok uzun.',
            'description.max' => 'Açıklama çok uzun.',
            'image.image' => 'Görsel dosyası geçerli bir resim olmalıdır.',
            'image.mimes' => 'Görsel dosyası jpeg, png, jpg, gif veya webp formatında olmalıdır.',
            'image.max' => 'Görsel dosyası 2MB\'dan büyük olamaz.',
            'initial_stock.integer' => 'Başlangıç stok tam sayı olmalıdır.',
            'initial_stock.min' => 'Başlangıç stok 0\'dan küçük olamaz.',
            'initial_stock.max' => 'Başlangıç stok çok büyük.',
            'critical_stock.integer' => 'Kritik stok tam sayı olmalıdır.',
            'critical_stock.min' => 'Kritik stok 0\'dan küçük olamaz.',
            'critical_stock.max' => 'Kritik stok çok büyük.',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }
            
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/products'), $imageName);
            $validated['image'] = 'uploads/products/' . $imageName;
        }

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Ürün başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Ürün başarıyla silindi.');
    }
}

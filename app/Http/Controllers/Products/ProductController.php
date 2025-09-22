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
        $products = Product::orderBy('created_at', 'desc')->paginate(15);
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
                'sku' => 'nullable|string|max:255',
                'unit' => 'nullable|string',
                'price' => 'nullable|numeric',
                'cost' => 'nullable|numeric',
                'category' => 'nullable|string',
                'brand' => 'nullable|string',
                'size' => 'nullable|string',
                'color' => 'nullable|string',
                'barcode' => 'nullable|string',
                'description' => 'nullable|string',
                'image' => 'nullable|image',
                'stock_quantity' => 'nullable|integer|min:0',
                'initial_stock' => 'nullable|integer',
                'critical_stock' => 'nullable|integer',
                'is_active' => 'boolean',
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
                'sku' => $product->sku,
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
            'sku' => 'nullable|string|max:255',
            'unit' => 'nullable|string',
            'price' => 'nullable|numeric',
            'cost' => 'nullable|numeric',
            'category' => 'nullable|string',
            'brand' => 'nullable|string',
            'size' => 'nullable|string',
            'color' => 'nullable|string',
            'barcode' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image',
            'initial_stock' => 'nullable|integer',
            'critical_stock' => 'nullable|integer',
            'is_active' => 'boolean',
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

    /**
     * Lookup product by barcode, QR code payload, or SKU/name for scanner.
     */
    public function lookup(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if ($q === '') {
            return response()->json(['success' => false, 'message' => 'Sorgu boş olamaz.'], 400);
        }

        // If QR payload is a URL like ronexcari.test/p/123 or contains ?code=XYZ, extract key
        $code = $q;
        if (preg_match('/code=([^&]+)/', $q, $m)) {
            $code = urldecode($m[1]);
        } elseif (preg_match('#/p/(\d+)#', $q, $m)) {
            $code = $m[1];
        }

        $product = Product::query()
            ->where('barcode', $code)
            ->orWhere('sku', $code)
            ->orWhere('id', is_numeric($code) ? (int) $code : 0)
            ->orWhere('name', 'like', "%{$code}%")
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Ürün bulunamadı.'], 404);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'price' => (float) $product->price,
                'vat_rate' => 20, // default
                'stock_quantity' => (int) ($product->stock_quantity ?? 0),
            ],
        ]);
    }

    /**
     * Quick QR preview page for mobile: show product details with actions.
     */
    public function quickView(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if ($q === '') {
            return redirect()->route('products.index')->with('error', 'QR verisi bulunamadı.');
        }

        // Reuse the same decode logic as lookup
        $code = $q;
        if (preg_match('/code=([^&]+)/', $q, $m)) {
            $code = urldecode($m[1]);
        } elseif (preg_match('#/p/(\d+)#', $q, $m)) {
            $code = $m[1];
        }

        $product = Product::query()
            ->where('barcode', $code)
            ->orWhere('sku', $code)
            ->orWhere('id', is_numeric($code) ? (int) $code : 0)
            ->orWhere('name', 'like', "%{$code}%")
            ->first();

        if (!$product) {
            return redirect()->route('products.index')->with('error', 'Ürün bulunamadı.');
        }

        return view('products.qr-preview', compact('product'));
    }
}

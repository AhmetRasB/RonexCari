<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\QrBarcodeService;

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

            // Generate permanent barcode and QR only at creation
            $service = app(QrBarcodeService::class);
            $barcodeValue = $product->sku ?: ('PRD-' . str_pad((string)$product->id, 8, '0', STR_PAD_LEFT));
            $qrValue = 'https://ronex.com.tr/products/' . $product->id;

            $barcodeSvg = $service->generateBarcodeSvg($barcodeValue);
            $qrSvg = $service->generateQrSvg($qrValue, 220);

            $barcodePath = 'uploads/barcodes/barcode_' . $product->id . '.svg';
            $qrPath = 'uploads/qrcodes/qr_' . $product->id . '.svg';

            $service->storeSvg($barcodeSvg, $barcodePath);
            $service->storeSvg($qrSvg, $qrPath);

            $product->permanent_barcode = $barcodeValue;
            $product->qr_code_value = $qrValue;
            $product->barcode_svg_path = $barcodePath;
            $product->qr_svg_path = $qrPath;
            $product->save();
            
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
        // Ensure QR/Barcode files exist for this product
        try {
            app(QrBarcodeService::class)->ensureProductCodes($product);
        } catch (\Throwable $e) {
            \Log::error('ensureProductCodes failed', ['product_id' => $product->id, 'error' => $e->getMessage()]);
        }
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

        // Never change permanent codes on edit
        unset($validated['permanent_barcode'], $validated['qr_code_value'], $validated['barcode_svg_path'], $validated['qr_svg_path']);
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

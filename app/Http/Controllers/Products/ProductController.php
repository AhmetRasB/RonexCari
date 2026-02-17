<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use App\Services\QrBarcodeService;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentAccountId = session('current_account_id');
        $allowedCategories = $this->getAllowedCategoriesForAccount($currentAccountId);
        $products = Product::with('colorVariants')
            ->when(!empty($allowedCategories), function($q) use ($allowedCategories){
                $q->whereIn('category', $allowedCategories);
            })
            ->when($request->filled('category'), function($q) use ($request) {
                $q->where('category', $request->get('category'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();
        $selectedCategory = $request->get('category');
        return view('products.index', compact('products', 'allowedCategories', 'selectedCategory'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currentAccountId = session('current_account_id');
        $allowedCategories = $this->getAllowedCategoriesForAccount($currentAccountId);
        return view('products.create', compact('allowedCategories'));
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
            $currentAccountId = session('current_account_id');
            $allowedCategories = $this->getAllowedCategoriesForAccount($currentAccountId);
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'nullable|string|max:255',
                'unit' => 'nullable|string',
                'price' => 'nullable|numeric',
                'cost' => 'nullable|numeric',
                'cost_currency' => 'nullable|string|in:TRY,USD,EUR',
                'price_currency' => 'nullable|string|in:TRY,USD,EUR',
                'category' => ['required','string', function($attr,$val,$fail) use ($allowedCategories){
                    if (!empty($allowedCategories) && !in_array($val, $allowedCategories, true)) {
                        $fail('Bu kategori mevcut hesap için izinli değil.');
                    }
                }],
                'brand' => 'nullable|string',
                'size' => 'nullable|string',
                'color' => 'nullable|string',
                'colors' => 'array',
                'colors.*' => 'string',
                'colors_input' => 'nullable|string',
                'color_variants' => 'array',
                'color_variants.*.color' => 'required|string',
                'color_variants.*.stock_quantity' => 'required|integer|min:0',
                'color_variants.*.critical_stock' => 'nullable|integer|min:0',
                'barcode' => 'nullable|string',
                'description' => 'nullable|string',
                'image' => 'nullable|image',
                'stock_quantity' => 'nullable|integer|min:0',
                'initial_stock' => 'nullable|integer|min:0',
                'critical_stock' => 'nullable|integer',
                'is_active' => 'boolean',
            ]);
            
            // Parse colors_input (comma-separated text) into colors array
            if (!empty($validated['colors_input'])) {
                $colorsFromInput = array_filter(array_map('trim', explode(',', $validated['colors_input'])));
                if (!empty($colorsFromInput)) {
                    $validated['colors'] = $colorsFromInput;
                }
                unset($validated['colors_input']);
            }
            
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

            // Helper to create a single product and assign codes
            $createOne = function(array $data) {
                // Sync stock_quantity and initial_stock to keep them consistent
                if (isset($data['initial_stock'])) {
                    $data['stock_quantity'] = $data['initial_stock'];
                } elseif (isset($data['stock_quantity'])) {
                    $data['initial_stock'] = $data['stock_quantity'];
                }
                
                // account_id default değeri
                if (!isset($data['account_id'])) {
                    $data['account_id'] = session('current_account_id', 1); // Default to account 1
                }
                
                // is_active default değeri
                if (!isset($data['is_active'])) {
                    $data['is_active'] = true;
                }
                
                // Özel ID atama mantığı
                $customId = $this->getCustomProductId($data['account_id'], $data['category']);
                if ($customId) {
                    // Manuel ID atama için DB transaction kullan
                    $product = new Product($data);
                    $product->id = $customId;
                    $product->save();
                } else {
                    $product = Product::create($data);
                }
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
                return $product;
            };

            $createdProducts = [];

            // If multiple colors selected, create one product with color variants
            $colors = array_filter(array_map('trim', (array)($validated['colors'] ?? [])));
            $colorVariants = $request->input('color_variants', []);
            
            if (!empty($colors) || !empty($colorVariants)) {
                // Create single product without color
                $productData = $validated;
                unset($productData['colors']); // Remove colors from main product
                unset($productData['color_variants']); // Remove color variants from main product
                $product = $createOne($productData);
                
                // Create color variants from new tag system
                if (!empty($colorVariants)) {
                    foreach ($colorVariants as $variant) {
                        if (!empty($variant['color'])) {
                            \App\Models\ProductColorVariant::create([
                                'product_id' => $product->id,
                                'color' => $variant['color'],
                                'stock_quantity' => $variant['stock_quantity'] ?? 0,
                                'critical_stock' => $variant['critical_stock'] ?? 0,
                                'is_active' => true
                            ]);
                        }
                    }
                } else {
                    // Fallback to old system
                    $stockPerColor = $validated['stock_quantity'] ?? 0;
                    $criticalStockPerColor = $validated['critical_stock'] ?? 0;
                    
                    foreach ($colors as $color) {
                        \App\Models\ProductColorVariant::create([
                            'product_id' => $product->id,
                            'color' => $color,
                            'stock_quantity' => $stockPerColor,
                            'critical_stock' => $criticalStockPerColor,
                            'is_active' => true
                        ]);
                    }
                }
                
                $createdProducts[] = $product;
            } else {
                $createdProducts[] = $createOne($validated);
            }

            \Log::info('Product created successfully', [
                'product_ids' => collect($createdProducts)->pluck('id'),
                'skus' => collect($createdProducts)->pluck('sku'),
                'name' => $createdProducts[0]->name,
                'category' => $createdProducts[0]->category,
                'brand' => $createdProducts[0]->brand,
                'variant_count' => count($createdProducts)
            ]);

            $successMessage = 'Ürün başarıyla oluşturuldu.';
            if (!empty($colors)) {
                $successMessage = 'Ürün ' . count($colors) . ' renk varyasyonu ile oluşturuldu.';
            }
            
            return redirect()->route('products.index')->with('success', $successMessage);
                
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
        // Load color variants
        $product->load('colorVariants');
        
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
        $currentAccountId = session('current_account_id');
        $allowedCategories = $this->getAllowedCategoriesForAccount($currentAccountId);
        
        // Load color variants for the product
        $product->load('colorVariants');
        
        return view('products.edit', compact('product', 'allowedCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $currentAccountId = session('current_account_id');
        $allowedCategories = $this->getAllowedCategoriesForAccount($currentAccountId);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'unit' => 'nullable|string',
            'price' => 'nullable|numeric',
            'cost' => 'nullable|numeric',
            'cost_currency' => 'nullable|string|in:TRY,USD,EUR',
            'price_currency' => 'nullable|string|in:TRY,USD,EUR',
            'category' => ['nullable','string', function($attr,$val,$fail) use ($allowedCategories){
                if (!empty($allowedCategories) && !in_array($val, $allowedCategories, true)) {
                    $fail('Bu kategori mevcut hesap için izinli değil.');
                }
            }],
            'brand' => 'nullable|string',
            'size' => 'nullable|string',
            'color' => 'nullable|string',
            'barcode' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image',
            'stock_quantity' => 'nullable|integer|min:0',
            'initial_stock' => 'nullable|integer|min:0',
            'critical_stock' => 'nullable|integer',
            'is_active' => 'boolean',
            'color_variants' => 'nullable|array',
            'color_variants.*' => 'nullable|array',
            'color_variants.*.stock_quantity' => 'nullable|numeric|min:0',
            'color_variants.*.critical_stock' => 'nullable|numeric|min:0',
            'color_variants.*.is_active' => 'nullable',
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
        
        // Handle color variants update if provided
        $colorVariantsData = $validated['color_variants'] ?? null;
        unset($validated['color_variants']);
        
        // Log color variants data for debugging
        if ($colorVariantsData) {
            \Log::info('Color variants update data received', [
                'product_id' => $product->id,
                'color_variants_data' => $colorVariantsData
            ]);
        }
        
        // Sync stock_quantity and initial_stock to keep them consistent
        if (isset($validated['initial_stock'])) {
            $validated['stock_quantity'] = $validated['initial_stock'];
        } elseif (isset($validated['stock_quantity'])) {
            $validated['initial_stock'] = $validated['stock_quantity'];
        }
        
        $product->update($validated);

        // Update color variants if provided
        if ($colorVariantsData) {
            foreach ($colorVariantsData as $variantId => $variantData) {
                $variant = \App\Models\ProductColorVariant::find($variantId);
                if ($variant && $variant->product_id === $product->id) {
                    $updateData = [];
                    
                    // Only update fields that are actually provided
                    if (array_key_exists('stock_quantity', $variantData)) {
                        $updateData['stock_quantity'] = (int) $variantData['stock_quantity'];
                    }
                    
                    if (array_key_exists('critical_stock', $variantData)) {
                        $updateData['critical_stock'] = (int) $variantData['critical_stock'];
                    }
                    
                    if (array_key_exists('is_active', $variantData)) {
                        $updateData['is_active'] = (bool) $variantData['is_active'];
                    }
                    
                    if (!empty($updateData)) {
                        \Log::info('Updating color variant', [
                            'variant_id' => $variantId,
                            'update_data' => $updateData
                        ]);
                        $variant->update($updateData);
                    }
                }
            }
        }

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
     * Quick update for critical stock and add to stock.
     */
    public function quickStockUpdate(Request $request, Product $product)
    {
        \Log::info('Quick stock update started', [
            'product_id' => $product->id,
            'request_data' => $request->all()
        ]);
        
        $data = $request->validate([
            'critical_stock' => 'nullable|integer|min:0',
            'add_stock' => 'nullable|integer|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'initial_stock' => 'nullable|integer|min:0',
        ]);

        $originalCriticalStock = (int) ($product->critical_stock ?? 0);
        $originalStockQuantity = (int) ($product->stock_quantity ?? 0);
        $originalInitialStock = (int) ($product->initial_stock ?? 0);

        \Log::info('Original values', [
            'critical_stock' => $originalCriticalStock,
            'stock_quantity' => $originalStockQuantity,
            'initial_stock' => $originalInitialStock
        ]);

        if (array_key_exists('critical_stock', $data) && $data['critical_stock'] !== null) {
            $product->critical_stock = (int) $data['critical_stock'];
            \Log::info('Updating critical_stock', ['new_value' => $product->critical_stock]);
        }

        if (array_key_exists('stock_quantity', $data) && $data['stock_quantity'] !== null) {
            $product->stock_quantity = (int) $data['stock_quantity'];
            $product->initial_stock = (int) $data['stock_quantity']; // Keep them in sync
        }

        if (array_key_exists('initial_stock', $data) && $data['initial_stock'] !== null) {
            $product->initial_stock = (int) $data['initial_stock'];
            $product->stock_quantity = (int) $data['initial_stock']; // Keep them in sync
        }

        if (!empty($data['add_stock'])) {
            $addStockAmount = (int) $data['add_stock'];
            
            // Color variants'ları da güncelle
            $colorVariants = $product->colorVariants;
            if ($colorVariants->count() > 0) {
                // Her renk varyantına direkt aynı miktarı ekle
                foreach ($colorVariants as $variant) {
                    $currentStock = (int) $variant->stock_quantity;
                    $newVariantStock = $currentStock + $addStockAmount;
                    $variant->update(['stock_quantity' => $newVariantStock]);
                }
                // Ana ürünün stok miktarını varyantların toplamına eşitle
                $product->stock_quantity = $colorVariants->sum('stock_quantity');
                $product->initial_stock = $colorVariants->sum('stock_quantity');
            } else {
                // Tek renkli ürün ise direkt ana ürüne ekle
                $newStock = $originalInitialStock + $addStockAmount;
                $product->stock_quantity = $newStock;
                $product->initial_stock = $newStock;
            }
        }

        \Log::info('Saving product', [
            'product_id' => $product->id,
            'critical_stock' => $product->critical_stock,
            'stock_quantity' => $product->stock_quantity,
            'initial_stock' => $product->initial_stock
        ]);
        
        $product->save();
        
        \Log::info('Product saved successfully', [
            'product_id' => $product->id,
            'final_critical_stock' => $product->fresh()->critical_stock
        ]);

        // Renk varyantlarının güncel stok bilgilerini al
        $colorVariants = $product->colorVariants->map(function($variant) {
            return [
                'id' => $variant->id,
                'color' => $variant->color,
                'stock_quantity' => (int) $variant->stock_quantity,
                'critical_stock' => (int) $variant->critical_stock,
                'is_active' => (bool) $variant->is_active
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Stok bilgileri güncellendi.',
            'data' => [
                'stock_quantity' => (int) ($product->stock_quantity ?? 0),
                'initial_stock' => (int) ($product->initial_stock ?? 0),
                'critical_stock' => (int) ($product->critical_stock ?? 0),
                'added' => (int) ($data['add_stock'] ?? 0),
                'original_stock_quantity' => $originalStockQuantity,
                'original_initial_stock' => $originalInitialStock,
                'original_critical_stock' => $originalCriticalStock,
                'color_variants' => $colorVariants,
                'has_color_variants' => $colorVariants->count() > 0
            ],
        ]);
    }

    /**
     * Get custom product ID based on account and category
     */
    private function getCustomProductId($accountId, $category): ?int
    {
        try {
            $account = \App\Models\Account::find($accountId);
            if (!$account) {
                return null;
            }
            
            // Ronex1'de Gömlek kategorisi için ID 1
            if ($account->code === 'RONEX1' && $category === 'Gömlek') {
                // ID 1 zaten kullanılıyor mu kontrol et
                $existingProduct = Product::where('id', 1)->first();
                if (!$existingProduct) {
                    return 1;
                }
            }
            
            // Ronex2'de herhangi bir kategori için ID 2
            if ($account->code === 'RONEX2') {
                // ID 2 zaten kullanılıyor mu kontrol et
                $existingProduct = Product::where('id', 2)->first();
                if (!$existingProduct) {
                    return 2;
                }
            }
            
            return null; // Özel ID atanmadı, auto-increment kullan
        } catch (\Throwable $e) {
            \Log::error('Custom product ID assignment failed', [
                'account_id' => $accountId,
                'category' => $category,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get allowed categories per account.
     */
    private function getAllowedCategoriesForAccount($accountId): array
    {
        if (!$accountId) {
            return [];
        }
        return ProductCategory::where('account_id', $accountId)
            ->orderBy('name')
            ->pluck('name')
            ->toArray();
    }

    /**
     * Bulk delete products
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = json_decode($request->input('ids'), true);
            
            if (empty($ids) || !is_array($ids)) {
                return redirect()->back()->with('error', 'Geçersiz seçim');
            }

            $deletedCount = Product::whereIn('id', $ids)->delete();
            
            return redirect()->route('products.index')
                ->with('success', $deletedCount . ' ürün başarıyla silindi');
        } catch (\Exception $e) {
            \Log::error('Bulk delete error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Silme işlemi sırasında bir hata oluştu');
        }
    }
}

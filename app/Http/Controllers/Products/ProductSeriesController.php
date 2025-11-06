<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\ProductSeries;
use App\Models\ProductSeriesItem;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Models\ProductBrand;

class ProductSeriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentAccountId = session('current_account_id');
        $allowedCategories = $this->getAllowedCategoriesForAccount($currentAccountId);
        
        // Eğer account'a ait kategori yoksa, hiçbir seri ürün gösterilmemeli
        if (empty($allowedCategories)) {
            $series = ProductSeries::where('id', 0)->paginate(15);
        } else {
        $series = ProductSeries::with('seriesItems')
                ->whereIn('category', $allowedCategories)
                ->when($request->filled('category'), function($q) use ($request, $allowedCategories) {
                    // Seçilen kategori, allowedCategories içinde olmalı
                    if (in_array($request->get('category'), $allowedCategories, true)) {
                $q->where('category', $request->get('category'));
                    }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();
        }
            
        $selectedCategory = $request->get('category');
        return view('products.series.index', compact('series', 'allowedCategories', 'selectedCategory'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $currentAccountId = session('current_account_id');
        $allowedCategories = $this->getAllowedCategoriesForAccount($currentAccountId);
        $parentId = $request->query('parent');
        $parentSeries = null;
        if ($parentId) {
            $parentSeries = ProductSeries::with(['seriesItems','colorVariants'])->find($parentId);
        }
        return view('products.series.create', compact('allowedCategories', 'parentSeries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentAccountId = session('current_account_id');
        $allowedCategories = $this->getAllowedCategoriesForAccount($currentAccountId);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category' => ['required','string', function($attr,$val,$fail) use ($allowedCategories){
                if (!empty($allowedCategories) && !in_array($val, $allowedCategories, true)) {
                    $fail('Bu kategori mevcut hesap için izinli değil.');
                }
            }],
            'brand' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'cost_currency' => 'nullable|string|in:TRY,USD,EUR',
            'price_currency' => 'nullable|string|in:TRY,USD,EUR',
            'image' => 'nullable|image',
            'is_active' => 'boolean',
            'sizes' => 'required|array|min:1',
            'sizes.*' => 'required|string',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|integer|min:1',
            'colors' => 'array',
            'colors.*' => 'string',
            'colors_input' => 'nullable|string',
            'color_variants' => 'array',
            'color_variants.*.color' => 'required|string',
            'color_variants.*.stock_quantity' => 'required|integer|min:0',
            'color_variants.*.critical_stock' => 'nullable|integer|min:0',
        ]);
        
        // Parse colors_input (comma-separated text) into colors array
        if (!empty($validated['colors_input'])) {
            $colorsFromInput = array_filter(array_map('trim', explode(',', $validated['colors_input'])));
            if (!empty($colorsFromInput)) {
                $validated['colors'] = $colorsFromInput;
            }
            unset($validated['colors_input']);
        }

        // Database column 'cost' is NOT NULL; if user leaves blank, default to 0.00
        if (!array_key_exists('cost', $validated) || $validated['cost'] === null) {
            $validated['cost'] = 0.00;
        }

        // Görsel yükleme
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // account_id default değeri
        if (!isset($validated['account_id'])) {
            $validated['account_id'] = session('current_account_id', 1);
        }

        // Marka: varsa bul/yoksa oluştur ve metin alanını normalize et
        if (!empty($validated['brand'])) {
            $brandName = trim($validated['brand']);
            if ($brandName !== '') {
                $brand = ProductBrand::firstOrCreate([
                    'account_id' => $validated['account_id'] ?? session('current_account_id'),
                    'name' => $brandName,
                ], [
                    'is_active' => true,
                ]);
                $validated['brand'] = $brand->name; // normalize to stored name
            }
        }

        // Eğer bir üst seri seçilerek gelindiyse, adı/barkodu üst seriden zorla kullan
        $parentSeriesId = $request->input('parent_series_id');
        if ($parentSeriesId) {
            $parent = ProductSeries::find($parentSeriesId);
            if ($parent) {
                $validated['name'] = $parent->name;
                $validated['barcode'] = $parent->barcode;
                // SKU boş ise üst serininkiyle eşle
                if (empty($validated['sku'])) {
                    $validated['sku'] = $parent->sku;
                }
                // Varsayılan olarak kategori/marka boşsa üst seriden kopyala
                $validated['category'] = $validated['category'] ?? $parent->category;
                $validated['brand'] = $validated['brand'] ?? $parent->brand;
                // Bağlantıyı kur
                $validated['parent_series_id'] = $parent->id;
            }
        }

        // Seri boyutunu belirle: girilen miktarların toplamı (yoksa beden sayısı)
        $quantities = (array) ($validated['quantities'] ?? []);
        $sumQuantities = 0;
        foreach ($quantities as $q) {
            $sumQuantities += (int) $q;
        }
        $validated['series_size'] = $sumQuantities > 0 ? $sumQuantities : count($validated['sizes']);

        // Seri oluştur
        $series = ProductSeries::create($validated);

        // Seri bedenlerini oluştur
        foreach ($validated['sizes'] as $index => $size) {
            ProductSeriesItem::create([
                'product_series_id' => $series->id,
                'size' => $size,
                'quantity_per_series' => $validated['quantities'][$index] ?? 1,
            ]);
        }

        // Renk varyantlarını oluştur
        $colors = array_filter(array_map('trim', (array)($validated['colors'] ?? [])));
        $colorVariants = $request->input('color_variants', []);
        
        if (!empty($colorVariants)) {
            // Yeni tag sistemi - her renk için ayrı stok miktarı
            foreach ($colorVariants as $variant) {
                if (!empty($variant['color'])) {
                    \App\Models\ProductSeriesColorVariant::create([
                        'product_series_id' => $series->id,
                        'color' => $variant['color'],
                        'stock_quantity' => $variant['stock_quantity'] ?? 0, // Her renk için ayrı stok
                        'critical_stock' => $variant['critical_stock'] ?? 0,
                        'is_active' => true
                    ]);
                }
            }
        } elseif (!empty($colors)) {
            // Eski sistem (fallback) - her renk için ayrı stok miktarı
            foreach ($colors as $color) {
                \App\Models\ProductSeriesColorVariant::create([
                    'product_series_id' => $series->id,
                    'color' => $color,
                    'stock_quantity' => 0, // Varsayılan stok
                    'critical_stock' => 0,
                    'is_active' => true
                ]);
            }
        }

        $successMessage = 'Seri başarıyla oluşturuldu.';
        if (!empty($colors)) {
            $successMessage = 'Seri ' . count($colors) . ' renk varyasyonu ile oluşturuldu.';
        }

        return redirect()->route('products.series.index')
            ->with('success', $successMessage);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductSeries $series)
    {
        $series->load('seriesItems');
        return view('products.series.show', compact('series'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductSeries $series)
    {
        $currentAccountId = session('current_account_id');
        $allowedCategories = $this->getAllowedCategoriesForAccount($currentAccountId);
        $series->load('seriesItems');
        return view('products.series.edit', compact('series', 'allowedCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductSeries $series)
    {
        $currentAccountId = session('current_account_id');
        $allowedCategories = $this->getAllowedCategoriesForAccount($currentAccountId);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category' => ['nullable','string', function($attr,$val,$fail) use ($allowedCategories){
                if (!empty($allowedCategories) && !in_array($val, $allowedCategories, true)) {
                    $fail('Bu kategori mevcut hesap için izinli değil.');
                }
            }],
            'brand' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'cost_currency' => 'nullable|string|in:TRY,USD,EUR',
            'price_currency' => 'nullable|string|in:TRY,USD,EUR',
            'image' => 'nullable|image',
            'is_active' => 'boolean',
            'color_variants' => 'array',
            'color_variants.*.stock_quantity' => 'integer|min:0',
            'color_variants.*.critical_stock' => 'integer|min:0',
            'color_variants.*.is_active' => 'boolean',
        ]);

        // Görsel yükleme
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Renk varyantlarını güncelle
        if (isset($validated['color_variants'])) {
            foreach ($validated['color_variants'] as $variantId => $variantData) {
                $variant = \App\Models\ProductSeriesColorVariant::find($variantId);
                if ($variant && $variant->product_series_id == $series->id) {
                    $variant->update([
                        'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                        'critical_stock' => $variantData['critical_stock'] ?? 0,
                        'is_active' => $variantData['is_active'] ?? true,
                    ]);
                }
            }
        }

        // Marka güncelle: varsa oluştur/bul
        if (!empty($validated['brand'])) {
            $brandName = trim($validated['brand']);
            if ($brandName !== '') {
                $brand = ProductBrand::firstOrCreate([
                    'account_id' => session('current_account_id'),
                    'name' => $brandName,
                ], [
                    'is_active' => true,
                ]);
                $validated['brand'] = $brand->name;
            }
        }

        $series->update($validated);

        return redirect()->route('products.series.index')
            ->with('success', 'Seri başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductSeries $series)
    {
        $series->delete();

        return redirect()->route('products.series.index')
            ->with('success', 'Seri başarıyla silindi.');
    }

    /**
     * Quick update for critical stock and add to stock.
     */
    public function quickStockUpdate(Request $request, ProductSeries $series)
    {
        \Log::info('Series quick stock update started', [
            'series_id' => $series->id,
            'request_data' => $request->all()
        ]);
        
        $data = $request->validate([
            'add_stock' => 'required|integer|min:0',
        ]);

        $addStockAmount = (int) $data['add_stock'];
        
        // Renk varyantlarını güncelle
        $colorVariants = $series->colorVariants;
        if ($colorVariants->count() > 0) {
            // Her renk varyantına direkt aynı miktarı ekle
            foreach ($colorVariants as $variant) {
                $currentStock = (int) $variant->stock_quantity;
                $newVariantStock = $currentStock + $addStockAmount;
                $variant->update(['stock_quantity' => $newVariantStock]);
            }
        } else {
            // Renk varyantı yoksa hata döndür
            return response()->json([
                'success' => false,
                'message' => 'Bu seri için renk varyantı bulunamadı.'
            ], 400);
        }

        \Log::info('Series quick stock update completed', [
            'series_id' => $series->id,
            'added_stock' => $addStockAmount,
            'total_variants' => $colorVariants->count()
        ]);

        // Renk varyantlarının güncel stok bilgilerini al
        $updatedVariants = $series->fresh()->colorVariants->map(function($variant) {
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
            'message' => 'Stok başarıyla eklendi.',
            'data' => [
                'stock_quantity' => $updatedVariants->sum('stock_quantity'),
                'color_variants' => $updatedVariants
            ]
        ]);
    }

    /**
     * Generate barcodes for product series
     */
    public function generateBarcodes(Request $request, ProductSeries $series)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:main,size,color,color_main,size_color',
            'items.*.identifier' => 'required|string',
            'items.*.count' => 'required|integer|min:1|max:200',
            'type' => 'required|in:barcode,qr,both',
            'paper' => 'nullable|string',
        ]);

        $rows = [];
        foreach ($validated['items'] as $item) {
            $rows[] = [
                'type' => $item['type'],
                'identifier' => $item['identifier'],
                'count' => (int) $item['count'],
                'series' => $series,
            ];
        }

        $type = $validated['type'];
        $paper = $validated['paper'] ?? 'A4';
        return view('products.series.barcodes.print', compact('rows', 'type', 'paper', 'series'));
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
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name')
            ->toArray();
    }

    /**
     * Bulk delete product series
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = json_decode($request->input('ids'), true);
            
            if (empty($ids) || !is_array($ids)) {
                return redirect()->back()->with('error', 'Geçersiz seçim');
            }

            $deletedCount = ProductSeries::whereIn('id', $ids)->delete();
            
            return redirect()->route('products.series.index')
                ->with('success', $deletedCount . ' seri başarıyla silindi');
        } catch (\Exception $e) {
            \Log::error('Bulk delete error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Silme işlemi sırasında bir hata oluştu');
        }
    }

    /**
     * Add new series size to existing series
     */
    public function addSize(Request $request, ProductSeries $series)
    {
        $validated = $request->validate([
            'series_size' => 'required|integer|min:2|max:50',
            'color_variants' => 'required|array|min:1',
            'color_variants.*.color' => 'required|string|max:255',
            'color_variants.*.stock_quantity' => 'required|integer|min:0',
            'color_variants.*.critical_stock' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Create new series with the same data but different series_size
            $newSeries = $series->replicate();
            $newSeries->series_size = $validated['series_size'];
            // Keep the same name to group under a single product name
            $newSeries->name = $series->name;
            // IMPORTANT: Reuse the same barcode so all sizes share one code
            $newSeries->barcode = $series->barcode;
            // Link as child
            $newSeries->parent_series_id = $series->parent_series_id ?: $series->id;
            $newSeries->save();

            // Create series items based on existing series items
            foreach ($series->seriesItems as $item) {
                $newSeries->seriesItems()->create([
                    'size' => $item->size,
                    'quantity_per_series' => $item->quantity_per_series,
                ]);
            }

            // Create color variants
            foreach ($validated['color_variants'] as $colorData) {
                $newSeries->colorVariants()->create([
                    'color' => $colorData['color'],
                    'stock_quantity' => $colorData['stock_quantity'],
                    'critical_stock' => $colorData['critical_stock'],
                    'is_active' => true,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Yeni seri boyutu başarıyla eklendi.',
                'data' => [
                    'series_id' => $newSeries->id,
                    'series_size' => $newSeries->series_size,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Add series size error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Seri boyutu eklenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
}

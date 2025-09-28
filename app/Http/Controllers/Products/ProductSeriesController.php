<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\ProductSeries;
use App\Models\ProductSeriesItem;
use Illuminate\Http\Request;

class ProductSeriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentAccountId = session('current_account_id');
        $allowedCategories = $this->getAllowedCategoriesForAccount($currentAccountId);
        
        $series = ProductSeries::with('seriesItems')
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
        return view('products.series.index', compact('series', 'allowedCategories', 'selectedCategory'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currentAccountId = session('current_account_id');
        $allowedCategories = $this->getAllowedCategoriesForAccount($currentAccountId);
        return view('products.series.create', compact('allowedCategories'));
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
            'image' => 'nullable|image',
            'series_type' => 'required|in:fixed,custom',
            'series_size' => 'nullable|integer|in:5,6,7',
            'stock_quantity' => 'required|integer|min:0',
            'critical_stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'sizes' => 'required|array|min:1',
            'sizes.*' => 'required|string',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|integer|min:1',
            'colors' => 'array',
            'colors.*' => 'string',
        ]);

        // Database column 'cost' is NOT NULL; if user leaves blank, default to 0.00
        if (!array_key_exists('cost', $validated) || $validated['cost'] === null) {
            $validated['cost'] = 0.00;
        }

        // Sabit seri için series_size zorunlu
        if ($validated['series_type'] === 'fixed' && empty($validated['series_size'])) {
            return back()->withErrors(['series_size' => 'Sabit seri için seri boyutu seçilmelidir.'])->withInput();
        }

        // Özel seri için seri boyutunu toplam miktar olarak ayarla
        if ($validated['series_type'] === 'custom') {
            $validated['series_size'] = array_sum($validated['quantities']);
        }

        // Görsel yükleme
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // account_id default değeri
        if (!isset($validated['account_id'])) {
            $validated['account_id'] = session('current_account_id', 1);
        }

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
        if (!empty($colors)) {
            $stockPerColor = $validated['stock_quantity'] ?? 0;
            $criticalStockPerColor = $validated['critical_stock'] ?? 0;
            
            foreach ($colors as $color) {
                \App\Models\ProductSeriesColorVariant::create([
                    'product_series_id' => $series->id,
                    'color' => $color,
                    'stock_quantity' => $stockPerColor,
                    'critical_stock' => $criticalStockPerColor,
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
            'image' => 'nullable|image',
            'stock_quantity' => 'required|integer|min:0',
            'critical_stock' => 'nullable|integer|min:0',
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
            
            // Ana seri stokunu renk varyantları toplamına eşitle
            $totalStock = $series->colorVariants->sum('stock_quantity');
            $validated['stock_quantity'] = $totalStock;
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
        $data = $request->validate([
            'critical_stock' => 'nullable|integer|min:0',
            'add_stock' => 'nullable|integer|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
        ]);

        $originalStockQuantity = (int) ($series->stock_quantity ?? 0);
        $originalCriticalStock = (int) ($series->critical_stock ?? 0);

        if (array_key_exists('critical_stock', $data) && $data['critical_stock'] !== null) {
            $series->critical_stock = (int) $data['critical_stock'];
        }

        if (array_key_exists('stock_quantity', $data) && $data['stock_quantity'] !== null) {
            $series->stock_quantity = (int) $data['stock_quantity'];
        }

        if (!empty($data['add_stock'])) {
            $addStockAmount = (int) $data['add_stock'];
            
            // Renk varyantlarını da güncelle
            $colorVariants = $series->colorVariants;
            if ($colorVariants->count() > 0) {
                // Her renk varyantına direkt aynı miktarı ekle
                foreach ($colorVariants as $variant) {
                    $currentStock = (int) $variant->stock_quantity;
                    $newVariantStock = $currentStock + $addStockAmount;
                    $variant->update(['stock_quantity' => $newVariantStock]);
                }
                // Ana serinin stok miktarını varyantların toplamına eşitle
                $series->stock_quantity = $colorVariants->sum('stock_quantity');
            } else {
                // Tek renkli seri ise direkt ana seriye ekle
                $series->stock_quantity = $originalStockQuantity + $addStockAmount;
            }
        }

        $series->save();

        // Renk varyantlarının güncel stok bilgilerini al
        $colorVariants = $series->colorVariants->map(function($variant) {
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
                'stock_quantity' => (int) ($series->stock_quantity ?? 0),
                'critical_stock' => (int) ($series->critical_stock ?? 0),
                'added' => (int) ($data['add_stock'] ?? 0),
                'original_stock_quantity' => $originalStockQuantity,
                'original_critical_stock' => $originalCriticalStock,
                'color_variants' => $colorVariants,
                'has_color_variants' => $colorVariants->count() > 0
            ],
        ]);
    }

    /**
     * Seri boyutuna göre varsayılan bedenleri getir
     */
    public function getDefaultSizes(Request $request)
    {
        $seriesSize = $request->get('series_size');
        $defaultSizes = ProductSeries::getDefaultSizesForSeries($seriesSize);
        
        return response()->json([
            'sizes' => $defaultSizes,
            'quantities' => array_fill(0, count($defaultSizes), 1)
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
        try {
            $code = \App\Models\Account::find($accountId)?->code;
        } catch (\Throwable $e) {
            $code = null;
        }
        if ($code === 'RONEX1') {
            return ['Gömlek'];
        }
        if ($code === 'RONEX2') {
            return ['Ceket', 'Takım Elbise', 'Pantalon'];
        }
        return [];
    }
}

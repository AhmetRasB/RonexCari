<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\ProductSeries;
use App\Models\ProductSeriesItem;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Models\ProductBrand;
use Illuminate\Support\Facades\DB;

class ProductSeriesController extends Controller
{
    private function currentAccountId(): int
    {
        $accountId = session('current_account_id');
        abort_unless($accountId, 403, 'Account not selected.');
        return (int) $accountId;
    }

    private function assertSeriesBelongsToCurrentAccount(ProductSeries $series): void
    {
        $accountId = $this->currentAccountId();
        if ((int) ($series->account_id ?? 0) !== $accountId) {
            abort(404);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentAccountId = $this->currentAccountId();
        $allowedCategories = $this->getAllowedCategoriesForAccount($currentAccountId);

        // Kategori manuel: kategori tablosu boş olsa bile serileri göster
        $series = ProductSeries::with(['seriesItems', 'colorVariants'])
            ->where('account_id', $currentAccountId)
            ->when($request->filled('category'), function ($q) use ($request) {
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
    public function create(Request $request)
    {
        $currentAccountId = $this->currentAccountId();
        $allowedCategories = $this->getAllowedCategoriesForAccount($currentAccountId);
        $parentId = $request->query('parent');
        $parentSeries = null;
        if ($parentId) {
            $parentSeries = ProductSeries::with(['seriesItems','colorVariants'])
                ->where('account_id', $currentAccountId)
                ->find($parentId);
        }
        return view('products.series.create', compact('allowedCategories', 'parentSeries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentAccountId = $this->currentAccountId();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
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
            'parent_series_id' => 'nullable|integer',
        ]);

        // Ensure sizes & quantities align
        if (count($validated['sizes'] ?? []) !== count($validated['quantities'] ?? [])) {
            return back()->withInput()->withErrors([
                'quantities' => 'Beden ve miktar sayısı eşleşmiyor.',
            ]);
        }
        
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

        // Enforce account_id from selected account (never from request)
        $validated['account_id'] = $currentAccountId;

        return DB::transaction(function () use ($request, $validated, $currentAccountId) {
            // Marka: varsa bul/yoksa oluştur ve metin alanını normalize et
            if (!empty($validated['brand'])) {
                $brandName = trim($validated['brand']);
                if ($brandName !== '') {
                    $brand = ProductBrand::firstOrCreate([
                        'account_id' => $currentAccountId,
                        'name' => $brandName,
                    ], [
                        'is_active' => true,
                    ]);
                    $validated['brand'] = $brand->name; // normalize to stored name
                }
            }

            // Kategori manuel: varsa bul/yoksa oluştur ve metin alanını normalize et
            if (!empty($validated['category'])) {
                $categoryName = trim($validated['category']);
                if ($categoryName !== '') {
                    $cat = ProductCategory::firstOrCreate([
                        'account_id' => $currentAccountId,
                        'name' => $categoryName,
                    ], [
                        'is_active' => true,
                    ]);
                    $validated['category'] = $cat->name;
                }
            }

            // Eğer bir üst seri seçilerek gelindiyse, adı/barkodu üst seriden zorla kullan
            $parentSeriesId = $request->input('parent_series_id');
            if ($parentSeriesId) {
                $parent = ProductSeries::where('account_id', $currentAccountId)->find($parentSeriesId);
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

        // Yeni oluşturulan seri için eksik varyant barkodlarını ata (seri barkodunu temel alarak kısaltılmış)
        $this->ensureVariantBarcodes($series);

        // Keep series stock_quantity consistent with variant totals
        $series->stock_quantity = (int) $series->colorVariants()->sum('stock_quantity');
        $series->save();

        $successMessage = 'Seri başarıyla oluşturuldu.';
        if (!empty($colors)) {
            $successMessage = 'Seri ' . count($colors) . ' renk varyasyonu ile oluşturuldu.';
        }

        return redirect()->route('products.series.index')
            ->with('success', $successMessage);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductSeries $series)
    {
        $this->assertSeriesBelongsToCurrentAccount($series);

        $currentAccountId = $this->currentAccountId();
        $series->load(['seriesItems', 'colorVariants', 'children.colorVariants', 'parent.children.colorVariants']);

        // Precompute grouping (avoid DB queries inside Blade)
        $groupSeries = collect();
        if ($series->parent_series_id && $series->parent) {
            $groupSeries = $series->parent->children->push($series->parent)->sortBy('series_size')->values();
        } else {
            $children = $series->children;
            if ($children->count() > 0) {
                $groupSeries = $children->push($series)->sortBy('series_size')->values();
            } elseif (!empty($series->barcode)) {
                $groupSeries = ProductSeries::with('colorVariants')
                    ->where('account_id', $currentAccountId)
                    ->where('barcode', $series->barcode)
                    ->orderBy('series_size')
                    ->get();
            }
        }

        $hasGroup = $groupSeries->count() > 1;
        $groupTotalStock = $hasGroup ? (int) $groupSeries->flatMap->colorVariants->sum('stock_quantity') : 0;

        return view('products.series.show', compact('series', 'groupSeries', 'hasGroup', 'groupTotalStock'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductSeries $series)
    {
        $this->assertSeriesBelongsToCurrentAccount($series);
        $currentAccountId = $this->currentAccountId();
        $allowedCategories = $this->getAllowedCategoriesForAccount($currentAccountId);
        $series->load(['seriesItems', 'colorVariants']);
        return view('products.series.edit', compact('series', 'allowedCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductSeries $series)
    {
        $this->assertSeriesBelongsToCurrentAccount($series);
        $currentAccountId = $this->currentAccountId();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'brand' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'cost_currency' => 'nullable|string|in:TRY,USD,EUR',
            'price_currency' => 'nullable|string|in:TRY,USD,EUR',
            'image' => 'nullable|image',
            'is_active' => 'boolean',
            'color_variants' => 'array',
            'color_variants.*.color' => 'nullable|string',
            'color_variants.*.stock_quantity' => 'integer|min:0',
            'color_variants.*.critical_stock' => 'integer|min:0',
            'color_variants.*.is_active' => 'boolean',
        ]);

        return DB::transaction(function () use ($request, $series, $validated, $currentAccountId) {
            // Görsel yükleme
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('products', 'public');
            }

            // Marka güncelle: varsa oluştur/bul
            if (!empty($validated['brand'])) {
                $brandName = trim($validated['brand']);
                if ($brandName !== '') {
                    $brand = ProductBrand::firstOrCreate([
                        'account_id' => $currentAccountId,
                        'name' => $brandName,
                    ], [
                        'is_active' => true,
                    ]);
                    $validated['brand'] = $brand->name;
                }
            }

            // Kategori manuel: varsa oluştur/bul
            if (!empty($validated['category'])) {
                $categoryName = trim($validated['category']);
                if ($categoryName !== '') {
                    $cat = ProductCategory::firstOrCreate([
                        'account_id' => $currentAccountId,
                        'name' => $categoryName,
                    ], [
                        'is_active' => true,
                    ]);
                    $validated['category'] = $cat->name;
                }
            }

            $series->update($validated);

            // Renk varyantlarını güncelle/oluştur (no implicit deletes: deletion must be explicit for safety)
            if (isset($validated['color_variants'])) {
                $series->loadMissing('colorVariants');
                $existingColorNames = $series->colorVariants->pluck('color')->map(fn($c) => trim((string)$c))->toArray();

                foreach ($validated['color_variants'] as $variantKey => $variantData) {
                    // New color rows: key is "new_x" and includes color
                    if (!empty($variantData['color'])) {
                        $colorName = trim((string) $variantData['color']);
                        if ($colorName !== '' && !in_array($colorName, $existingColorNames, true)) {
                            $series->colorVariants()->create([
                                'color' => $colorName,
                                'stock_quantity' => (int) ($variantData['stock_quantity'] ?? 0),
                                'critical_stock' => (int) ($variantData['critical_stock'] ?? 0),
                                'is_active' => (bool) ($variantData['is_active'] ?? true),
                            ]);
                            $existingColorNames[] = $colorName;
                        }
                        continue;
                    }

                    // Existing rows: numeric key = variant id
                    if (is_numeric($variantKey)) {
                        $variantId = (int) $variantKey;
                        $variant = \App\Models\ProductSeriesColorVariant::where('product_series_id', $series->id)->find($variantId);
                        if ($variant) {
                            $variant->update([
                                'stock_quantity' => (int) ($variantData['stock_quantity'] ?? $variant->stock_quantity),
                                'critical_stock' => (int) ($variantData['critical_stock'] ?? $variant->critical_stock),
                                'is_active' => (bool) ($variantData['is_active'] ?? $variant->is_active),
                            ]);
                        }
                    }
                }
            }

            // Keep series stock_quantity consistent with variant totals
            $series->stock_quantity = (int) $series->colorVariants()->sum('stock_quantity');
            $series->save();

            // Güncel seri için eksik varyant barkodlarını ata (seri barkodunu temel alarak kısaltılmış)
            $this->ensureVariantBarcodes($series);

            return redirect()->route('products.series.index')
                ->with('success', 'Seri başarıyla güncellendi.');
        });
    }

    /**
     * Eksik renk varyant barkodlarını seri barkodunu temel alarak kısa formatta üretir.
     * Ör: Seri barkodu GF01 ise varyantlar GF011, GF012, GF013 ... şeklinde atanır.
     */
    private function ensureVariantBarcodes(ProductSeries $series): void
    {
        $base = $series->barcode ?: ($series->sku ?: ('S' . $series->id));
        $base = preg_replace('/\s+/', '', (string)$base);
        if ($base === '') {
            $base = 'S' . $series->id;
        }
        $variants = $series->colorVariants()->get();
        if ($variants->isEmpty()) {
            return;
        }
        // Mevcut suffix numaralarını topla ve uygunsuz (SV..., PV..., base ile başlamayan) barkodları normalize et
        $existing = \App\Models\ProductSeriesColorVariant::where('product_series_id', $series->id)
            ->whereNotNull('barcode')
            ->pluck('barcode')
            ->map(function ($code) use ($base) {
                if (strpos($code, $base) === 0) {
                    $suffix = substr($code, strlen($base));
                    return ctype_digit($suffix) && $suffix !== '' ? (int)$suffix : null;
                }
                return null;
            })
            ->filter()
            ->all();
        $next = empty($existing) ? 1 : (max($existing) + 1);

        foreach ($variants as $variant) {
            $needsRecode = empty($variant->barcode)
                || preg_match('/^(SV|PV)/', (string)$variant->barcode) === 1
                || strpos((string)$variant->barcode, $base) !== 0;
            if ($needsRecode) {
                $candidate = $base . $next;
                // Benzersizliği garanti et
                while (\App\Models\ProductSeriesColorVariant::where('barcode', $candidate)->exists()) {
                    $next++;
                    $candidate = $base . $next;
                }
                $variant->barcode = $candidate;
                // Varyanta özel QR URL de üret
                if (empty($variant->qr_code_value)) {
                    $variant->qr_code_value = route('products.series.color', ['series' => $series->id, 'variant' => $variant->id]);
                }
                $variant->save();
                $next++;
            }
        }
    }

    /**
     * Manually normalize variant barcodes for a specific series (short base+counter format).
     */
    public function normalizeBarcodes(ProductSeries $series)
    {
        $this->assertSeriesBelongsToCurrentAccount($series);
        $this->ensureVariantBarcodes($series);
        return back()->with('success', 'Varyant barkodları kısa formata dönüştürüldü.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductSeries $series)
    {
        $this->assertSeriesBelongsToCurrentAccount($series);
        $series->delete();

        return redirect()->route('products.series.index')
            ->with('success', 'Seri başarıyla silindi.');
    }

    /**
     * Quick update for critical stock and add to stock.
     */
    public function quickStockUpdate(Request $request, ProductSeries $series)
    {
        $this->assertSeriesBelongsToCurrentAccount($series);
        \Log::info('Series quick stock update started', [
            'series_id' => $series->id,
            'request_data' => $request->all()
        ]);
        
        $data = $request->validate([
            'add_stock' => 'required|integer|min:1',
        ]);

        $addStockAmount = (int) $data['add_stock'];

        try {
            DB::transaction(function () use ($series, $addStockAmount) {
                $variants = $series->colorVariants()->lockForUpdate()->get();
                if ($variants->count() < 1) {
                    throw new \RuntimeException('Bu seri için renk varyantı bulunamadı.');
                }
                foreach ($variants as $variant) {
                    $variant->increment('stock_quantity', $addStockAmount);
                }
                $series->stock_quantity = (int) $series->colorVariants()->sum('stock_quantity');
                $series->save();
            });
        } catch (\Throwable $e) {
            \Log::warning('Series quick stock update failed', [
                'series_id' => $series->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }

        \Log::info('Series quick stock update completed', [
            'series_id' => $series->id,
            'added_stock' => $addStockAmount,
            'total_variants' => $series->colorVariants()->count()
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

        // Kategori listesi: hem kategori tablosundan hem de serilerde kayıtlı kategorilerden gelsin
        $fromCategories = ProductCategory::where('account_id', $accountId)
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        $fromSeries = ProductSeries::where('account_id', $accountId)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        $merged = array_values(array_unique(array_merge($fromCategories, $fromSeries)));
        sort($merged);
        return $merged;
    }

    /**
     * Bulk delete product series
     */
    public function bulkDelete(Request $request)
    {
        $currentAccountId = $this->currentAccountId();
        try {
            $ids = json_decode($request->input('ids'), true);
            
            if (empty($ids) || !is_array($ids)) {
                return redirect()->back()->with('error', 'Geçersiz seçim');
            }

            $deletedCount = ProductSeries::where('account_id', $currentAccountId)->whereIn('id', $ids)->delete();
            
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
        $this->assertSeriesBelongsToCurrentAccount($series);
        $validated = $request->validate([
            'series_size' => 'required|integer|min:2|max:50',
            'color_variants' => 'required|array|min:1',
            'color_variants.*.color' => 'required|string|max:255',
            'color_variants.*.stock_quantity' => 'required|integer|min:0',
            'color_variants.*.critical_stock' => 'required|integer|min:0',
        ]);

        try {
            $newSeries = DB::transaction(function () use ($series, $validated) {
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

                // Keep series stock consistent + ensure barcodes
                $this->ensureVariantBarcodes($newSeries);
                $newSeries->stock_quantity = (int) $newSeries->colorVariants()->sum('stock_quantity');
                $newSeries->save();

                return $newSeries;
            });

            return response()->json([
                'success' => true,
                'message' => 'Yeni seri boyutu başarıyla eklendi.',
                'data' => [
                    'series_id' => $newSeries->id,
                    'series_size' => $newSeries->series_size,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Add series size error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Seri boyutu eklenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
}

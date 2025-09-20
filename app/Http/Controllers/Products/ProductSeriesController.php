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
    public function index()
    {
        $series = ProductSeries::with('seriesItems')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('products.series.index', compact('series'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.series.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
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
        ]);

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

        return redirect()->route('products.series.index')
            ->with('success', 'Seri başarıyla oluşturuldu.');
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
        $series->load('seriesItems');
        return view('products.series.edit', compact('series'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductSeries $series)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'brand' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image',
            'stock_quantity' => 'required|integer|min:0',
            'critical_stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Görsel yükleme
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
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
}

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSeries;
use App\Services\QrBarcodeService;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function index()
    {
        // Tekli ürünler artık gösterilmiyor
        $products = collect();
        $series = ProductSeries::with('seriesItems')->orderBy('name')->get(['id','name','sku','barcode','price','stock_quantity','series_size']);
        return view('barcode.index', compact('products', 'series'));
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.type' => 'required|in:product,series',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'layout' => 'required|in:a4-10',
        ]);

        $expanded = [];
        foreach ($validated['items'] as $row) {
            if ($row['type'] === 'product') {
                // Normal ürün - renk varyantları varsa her renk için ayrı etiket
                $item = Product::with('colorVariants')->find($row['id']);
                if ($item) {
                    // Kısa barkod oluştur (5 karakter)
                    $shortBarcode = $this->generateShortBarcode('P', $item->id);
                    
                    if ($item->colorVariants && $item->colorVariants->count() > 0) {
                        // Renk varyantı varsa her renk için
                        foreach ($item->colorVariants as $colorVariant) {
                            for ($i = 0; $i < $row['quantity']; $i++) {
                                $expanded[] = [
                                    'type' => 'product',
                                    'item' => $item,
                                    'label' => $item->name . ($item->size ? ' - ' . $item->size : '') . ' - RENK: ' . $colorVariant->color,
                                    'code' => $item->sku ?: $shortBarcode,
                                    'barcode' => $shortBarcode
                                ];
                            }
                        }
                    } else {
                        // Renk yoksa normal
                        for ($i = 0; $i < $row['quantity']; $i++) {
                            $expanded[] = [
                                'type' => 'product',
                                'item' => $item,
                                'label' => $item->name . ($item->size ? ' - ' . $item->size : ''),
                                'code' => $item->sku ?: $shortBarcode,
                                'barcode' => $shortBarcode
                            ];
                        }
                    }
                }
            } elseif ($row['type'] === 'series') {
                // Seri - her paket için 1 dış paket + tüm renk x beden kombinasyonları
                $series = ProductSeries::with(['seriesItems', 'colorVariants'])->find($row['id']);
                if ($series) {
                    // Kısa barkod oluştur (5 karakter)
                    $shortBarcode = $this->generateShortBarcode('S', $series->id);
                    
                    // Seri bedenlerini ve renklerini al
                    $sizes = $series->seriesItems->pluck('size')->toArray();
                    $colors = $series->colorVariants->pluck('color')->toArray();
                    $seriesSize = $series->series_size ?? 0;
                    
                    // Her paket için
                    for ($packageIndex = 0; $packageIndex < $row['quantity']; $packageIndex++) {
                        // 1. Dış paket etiketi
                        if (count($colors) > 0) {
                            // Renk varsa renk bilgisiyle
                            $colorList = implode(', ', $colors);
                            $sizeList = implode(' ', $sizes);
                            $expanded[] = [
                                'type' => 'series_main',
                                'item' => $series,
                                'label' => $series->name . ' - ' . ($seriesSize > 0 ? $seriesSize . "'li " : '') . 'SERİ ' . date('Y') . ' - Renkler: ' . $colorList . ' - Bedenler: ' . $sizeList,
                                'code' => $series->sku ?: $shortBarcode,
                                'barcode' => $shortBarcode
                            ];
                            
                            // Her renk x Her beden kombinasyonu
                            foreach ($colors as $color) {
                                foreach ($sizes as $size) {
                                    $expanded[] = [
                                        'type' => 'series_size',
                                        'item' => $series,
                                        'label' => $series->name . ' - RENK: ' . $color . ' - BEDEN: ' . $size,
                                        'code' => $series->sku ?: $shortBarcode,
                                        'barcode' => $shortBarcode
                                    ];
                                }
                            }
                        } else {
                            // Renk yoksa sadece bedenler
                            $sizeList = implode(' ', $sizes);
                            $expanded[] = [
                                'type' => 'series_main',
                                'item' => $series,
                                'label' => $series->name . ' - ' . ($seriesSize > 0 ? $seriesSize . "'li " : '') . 'SERİ ' . date('Y') . ' - Bedenler: ' . $sizeList,
                                'code' => $series->sku ?: $shortBarcode,
                                'barcode' => $shortBarcode
                            ];
                            
                            // Beden etiketleri
                            foreach ($sizes as $size) {
                                $expanded[] = [
                                    'type' => 'series_size',
                                    'item' => $series,
                                    'label' => $series->name . ' - BEDEN: ' . $size,
                                    'code' => $series->sku ?: $shortBarcode,
                                    'barcode' => $shortBarcode
                                ];
                            }
                        }
                    }
                }
            }
        }

        return view('barcode.print-a4-10', [
            'items' => $expanded,
        ]);
    }

    /**
     * Kısa barkod oluştur (5 karakter)
     * Format: P1234 (Product) veya S1234 (Series)
     */
    private function generateShortBarcode($type, $id)
    {
        // ID'yi 4 haneli yap
        $paddedId = str_pad((string)$id, 4, '0', STR_PAD_LEFT);
        
        // 5 karakterli barkod: P1234 veya S1234
        return $type . $paddedId;
    }


    /**
     * Lookup product or series by barcode and return redirect URL
     */
    public function lookupByBarcode(Request $request)
    {
        try {
            $barcode = $request->get('barcode', '');
            
            if (empty($barcode)) {
                return response()->json(['error' => 'Barcode is required'], 400);
            }

            $currentAccountId = session('current_account_id');

            // Yalnızca seri ürünlerde arama yap
            $series = ProductSeries::where('barcode', $barcode)
                ->where('is_active', true)
                ->when($currentAccountId !== null, function($q) use ($currentAccountId) {
                    $q->where('account_id', $currentAccountId);
                })
                ->when($currentAccountId === null, function($q) {
                    // Eğer hesap seçili değilse, tüm ürünleri getir
                    $q->whereNotNull('account_id');
                })
                ->first();

            if ($series) {
                return response()->json([
                    'type' => 'series',
                    'id' => $series->id,
                    'name' => $series->name,
                    'redirect_url' => route('products.series.show', $series->id)
                ]);
            }

            // Seri olarak kısmi eşleşme ara
            $series = ProductSeries::where(function($q) use ($barcode) {
                    $q->where('barcode', 'like', "%{$barcode}%")
                      ->orWhere('sku', $barcode)
                      ->orWhere('sku', 'like', "%{$barcode}%");
                })
                ->where('is_active', true)
                ->when($currentAccountId !== null, function($q) use ($currentAccountId) {
                    $q->where('account_id', $currentAccountId);
                })
                ->when($currentAccountId === null, function($q) {
                    $q->whereNotNull('account_id');
                })
                ->first();

            if ($series) {
                return response()->json([
                    'type' => 'series',
                    'id' => $series->id,
                    'name' => $series->name,
                    'redirect_url' => route('products.series.show', $series->id)
                ]);
            }

            $series = ProductSeries::where(function($q) use ($barcode) {
                    $q->where('barcode', 'like', "%{$barcode}%")
                      ->orWhere('sku', $barcode)
                      ->orWhere('sku', 'like', "%{$barcode}%");
                })
                ->where('is_active', true)
                ->when($currentAccountId !== null, function($q) use ($currentAccountId) {
                    $q->where('account_id', $currentAccountId);
                })
                ->when($currentAccountId === null, function($q) {
                    // Eğer hesap seçili değilse, tüm ürünleri getir
                    $q->whereNotNull('account_id');
                })
                ->first();

            if ($series) {
                return response()->json([
                    'type' => 'series',
                    'id' => $series->id,
                    'name' => $series->name,
                    'redirect_url' => route('products.series.show', $series->id)
                ]);
            }

            return response()->json(['error' => 'Product or series not found'], 404);

        } catch (\Exception $e) {
            \Log::error('Barcode lookup failed', ['error' => $e->getMessage(), 'barcode' => $request->get('barcode')]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}



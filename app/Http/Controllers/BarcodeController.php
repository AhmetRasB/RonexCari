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
        $products = Product::orderBy('name')->get(['id','name','size','sku','barcode','price','stock_quantity']);
        $series = ProductSeries::orderBy('name')->get(['id','name','sku','barcode','price','stock_quantity']);
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
                // Normal ürün - sadece belirtilen adet kadar
                $item = Product::find($row['id']);
                if ($item) {
                    // Kısa barkod oluştur (5 karakter)
                    $shortBarcode = $this->generateShortBarcode('P', $item->id);
                    
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
            } elseif ($row['type'] === 'series') {
                // Seri - her paket için 1 dış paket + tüm beden etiketleri
                $series = ProductSeries::find($row['id']);
                if ($series) {
                    // Kısa barkod oluştur (5 karakter)
                    $shortBarcode = $this->generateShortBarcode('S', $series->id);
                    
                    // Seri bedenlerini al
                    $sizes = $series->seriesItems->pluck('size')->toArray();
                    
                    // Her paket için
                    for ($packageIndex = 0; $packageIndex < $row['quantity']; $packageIndex++) {
                        // 1. Dış paket etiketi (renk varsa renk, yoksa ana)
                        if ($series->colorVariants->count() > 0) {
                            foreach ($series->colorVariants as $color) {
                                $expanded[] = [
                                    'type' => 'series_main',
                                    'item' => $series,
                                    'label' => $color->color . ' SERİ ' . date('Y'),
                                    'code' => $series->sku ?: $shortBarcode,
                                    'barcode' => $shortBarcode
                                ];
                                
                                // Her renk için beden etiketleri
                                foreach ($sizes as $size) {
                                    $expanded[] = [
                                        'type' => 'series_size',
                                        'item' => $series,
                                        'label' => $color->color . ' ' . $size . ' ' . $series->name,
                                        'code' => $series->sku ?: $shortBarcode,
                                        'barcode' => $shortBarcode
                                    ];
                                }
                            }
                        } else {
                            // Renk yoksa sadece ana seri
                            $expanded[] = [
                                'type' => 'series_main',
                                'item' => $series,
                                'label' => 'ANA SERİ ' . date('Y'),
                                'code' => $series->sku ?: $shortBarcode,
                                'barcode' => $shortBarcode
                            ];
                            
                            // Beden etiketleri
                            foreach ($sizes as $size) {
                                $expanded[] = [
                                    'type' => 'series_size',
                                    'item' => $series,
                                    'label' => $size . ' ' . $series->name,
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

    public function test(QrBarcodeService $service)
    {
        $barcodeValue = 'TEST-123456';
        $qrValue = 'https://example.com/test';

        $barcodeSvg = $service->generateBarcodeSvg($barcodeValue);
        $qrSvg = $service->generateQrSvg($qrValue, 220);

        return view('barcode.test', [
            'barcodeSvg' => $barcodeSvg,
            'qrSvg' => $qrSvg,
            'barcodeValue' => $barcodeValue,
            'qrValue' => $qrValue,
        ]);
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

            // First, try to find by exact barcode match in products
            $product = Product::where('barcode', $barcode)
                ->where('is_active', true)
                ->when($currentAccountId !== null, function($q) use ($currentAccountId) {
                    $q->where('account_id', $currentAccountId);
                })
                ->when($currentAccountId === null, function($q) {
                    // Eğer hesap seçili değilse, tüm ürünleri getir
                    $q->whereNotNull('account_id');
                })
                ->first();

            if ($product) {
                return response()->json([
                    'type' => 'product',
                    'id' => $product->id,
                    'name' => $product->name,
                    'redirect_url' => route('products.show', $product->id)
                ]);
            }

            // If not found in products, try product series
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

            // If still not found, try partial matches or other fields
            $product = Product::where(function($q) use ($barcode) {
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

            if ($product) {
                return response()->json([
                    'type' => 'product',
                    'id' => $product->id,
                    'name' => $product->name,
                    'redirect_url' => route('products.show', $product->id)
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



<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\QrBarcodeService;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->get(['id','name','size','sku','permanent_barcode']);
        return view('barcode.index', compact('products'));
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'layout' => 'required|in:a4-10',
        ]);

        $products = Product::whereIn('id', collect($validated['items'])->pluck('product_id'))->get()->keyBy('id');

        $expanded = [];
        foreach ($validated['items'] as $row) {
            $product = $products[$row['product_id']];
            for ($i = 0; $i < $row['quantity']; $i++) {
                $expanded[] = $product;
            }
        }

        return view('barcode.print-a4-10', [
            'items' => $expanded,
        ]);
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
}



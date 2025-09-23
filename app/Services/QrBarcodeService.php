<?php

namespace App\Services;

use App\Models\Product;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Picqer\Barcode\BarcodeGeneratorSVG;

class QrBarcodeService
{
    public function generateBarcodeSvg(string $content): string
    {
        $generator = new BarcodeGeneratorSVG();
        // Use Code128 for general-purpose alphanumeric barcodes
        return $generator->getBarcode($content, $generator::TYPE_CODE_128, 2, 60);
    }

    public function generateQrSvg(string $content, int $size = 220): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        return $writer->writeString($content);
    }

    public function storeSvg(string $svg, string $path): string
    {
        $fullPath = public_path($path);
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            @mkdir($directory, 0777, true);
        }
        file_put_contents($fullPath, $svg, LOCK_EX);
        return $path;
    }

    public function ensureProductCodes(Product $product): void
    {
        $barcodeValue = $product->permanent_barcode ?: ($product->sku ?: ('PRD-' . str_pad((string)$product->id, 8, '0', STR_PAD_LEFT)));
        $qrValue = $product->qr_code_value ?: ('https://ronex.com.tr/products/' . $product->id);

        $barcodePath = $product->barcode_svg_path ?: 'uploads/barcodes/barcode_' . $product->id . '.svg';
        $qrPath = $product->qr_svg_path ?: 'uploads/qrcodes/qr_' . $product->id . '.svg';

        // Create barcode file if missing
        $fullBarcode = public_path($barcodePath);
        if (!file_exists($fullBarcode)) {
            $barcodeSvg = $this->generateBarcodeSvg($barcodeValue);
            $this->storeSvg($barcodeSvg, $barcodePath);
        }

        // Create QR file if missing
        $fullQr = public_path($qrPath);
        if (!file_exists($fullQr)) {
            $qrSvg = $this->generateQrSvg($qrValue, 220);
            $this->storeSvg($qrSvg, $qrPath);
        }

        // Persist fields if not set
        $product->permanent_barcode = $barcodeValue;
        $product->qr_code_value = $qrValue;
        $product->barcode_svg_path = $barcodePath;
        $product->qr_svg_path = $qrPath;
        $product->save();
    }
}



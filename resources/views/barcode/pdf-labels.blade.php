<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barkod Etiketleri</title>
    <style>
        @page {
            margin: 5mm; /* small page margin so borders print cleanly */
            size: A4 landscape;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 10px;
            background: white;
        }
        
        .label-container {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
            page-break-inside: avoid;
        }
        
        .label {
            width: 5cm;               /* exact width */
            height: 3cm;              /* exact height */
            border: 1px solid #000;   /* outer border */
            box-sizing: border-box;
            padding: 3mm;            /* safe inner padding */
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            position: relative;
            page-break-inside: avoid;
            background: white;
            margin: 2mm;              /* small gap between labels */
        }
        
        .label-content {
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
        }
        
        .label-header {
            margin-bottom: 2mm;
            padding-right: 18mm; /* leave space for QR at right */
        }
        
        .product-category {
            font-size: 10pt;
            font-weight: bold;
            color: #000;
            margin-bottom: 1mm;
            line-height: 1.1;
        }
        
        .product-name {
            font-size: 9pt;
            font-weight: bold;
            color: #000;
            margin-bottom: 1mm;
            line-height: 1.1;
        }
        
        .product-info {
            font-size: 8pt;
            font-weight: bold;
            color: #000;
            margin-bottom: 0.5mm;
            line-height: 1.1;
        }
        
        .colors-info {
            font-size: 8pt;
            font-weight: bold;
            color: #000;
            margin-bottom: 0.5mm;
            line-height: 1.1;
        }
        
        .sizes-info {
            font-size: 8pt;
            font-weight: bold;
            color: #000;
            margin-bottom: 0.5mm;
            line-height: 1.1;
        }
        
        .label-footer {
            position: absolute;
            left: 3mm; right: 3mm; bottom: 3mm; /* barcode sits at bottom */
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .barcode-section {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            flex: 1;
        }
        
        .qr-section {
            position: absolute;
            top: 3mm;
            right: 3mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 18mm;
        }
        
        .barcode-image {
            width: 100%;
            height: 12mm; /* fits into 3cm height bottom area */
            margin-bottom: 1mm;
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }
        
        .barcode-image img {
            width: 100%;
            height: 12mm;
            object-fit: contain;
        }
        
        .qr-image {
            width: 18mm;
            height: 18mm;
            margin-bottom: 1mm;
            object-fit: contain;
        }
        
        .barcode-text {
            font-size: 9pt;
            color: #000;
            text-align: left;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            margin-bottom: 0.5mm;
        }
        
        .company-name {
            font-size: 8pt;
            color: #000;
            text-align: center;
            font-weight: bold;
            width: 100%;
        }
        
        /* Print optimizations */
        @media print {
            .label {
                border: 1px solid #ccc;
            }
            
            .barcode-image,
            .qr-image {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
        
        /* Ensure images are crisp in PDF */
        .barcode-image img,
        .qr-image img {
            max-width: 100%;
            height: auto;
        }
        
        /* Print-specific optimizations */
        @media print {
            .barcode-image img,
            .qr-image img {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="label-container">
        @foreach($items as $item)
            @php
                $product = null;
                $series = null;
                $labelData = [];
                
                if ($item['type'] === 'product') {
                    $product = $products->find($item['id']);
                    if ($product) {
                        $labelData = [
                            'name' => $product->name,
                            'sizes' => $product->size ?: 'Tek Boyut',
                            'category' => $product->category ?: 'Kategori Yok',
                            'color' => $product->color ?? '',
                            'barcode' => $product->barcode ?: ($product->sku ?: ('P' . str_pad($product->id, 4, '0', STR_PAD_LEFT))),
                            'qr_url' => route('products.show', $product->id),
                            'type' => 'product'
                        ];
                    }
                } else {
                    $seriesItem = $series->firstWhere('id', $item['id']);
                    if ($seriesItem) {
                        $sizes = $seriesItem->seriesItems->pluck('size')->filter()->values()->all();
                        $sizesText = !empty($sizes) ? implode(' ', $sizes) : 'Beden Yok';
                        
                        // Get colors from color variants
                        $colors = $seriesItem->colorVariants->pluck('color')->filter()->values()->all();
                        $colorsText = !empty($colors) ? implode(', ', $colors) : '';
                        
                        $labelData = [
                            'name' => $seriesItem->name,
                            'sizes' => $sizesText,
                            'category' => $seriesItem->category ?: 'Kategori Yok',
                            'colors' => $colorsText,
                            'series_info' => ($seriesItem->series_size ? $seriesItem->series_size . '\'li SERI ' : 'SERI ') . date('Y'),
                            'barcode' => $seriesItem->barcode ?: ($seriesItem->sku ?: ('S' . str_pad($seriesItem->id, 4, '0', STR_PAD_LEFT))),
                            'qr_url' => route('products.series.show', $seriesItem->id),
                            'type' => 'series'
                        ];
                    }
                }
            @endphp
            
            @if($labelData)
                @for($i = 0; $i < $item['count']; $i++)
                    <div class="label">
                        <div class="label-content">
                            <div class="label-header">
                                <div class="product-category">{{ $labelData['category'] }}</div>
                                <div class="product-name">{{ $labelData['name'] }}</div>
                                @if($labelData['type'] === 'series')
                                    <div class="product-info">{{ $labelData['series_info'] ?? 'SERI ' . date('Y') }}</div>
                                    @if(isset($labelData['colors']) && !empty($labelData['colors']))
                                        <div class="colors-info">Renkler: {{ $labelData['colors'] }}</div>
                                    @endif
                                    <div class="sizes-info">Bedenler: {{ $labelData['sizes'] }}</div>
                                @else
                                    @if(isset($labelData['color']) && !empty($labelData['color']))
                                        <div class="product-info">RENK: {{ $labelData['color'] }}</div>
                                    @endif
                                    <div class="product-info">BEDEN: {{ $labelData['sizes'] }}</div>
                                    <div class="product-info">Seri: 5'li</div>
                                @endif
                            </div>
                            
                            <div class="label-footer">
                                <div class="barcode-section">
                                    <div class="barcode-image">
                                        @php
                                            try {
                                                $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                                                $barcodeBinary = $generator->getBarcode($labelData['barcode'], $generator::TYPE_CODE_128, 2, 50);
                                                $barcodeDataUri = 'data:image/png;base64,' . base64_encode($barcodeBinary);
                                            } catch (\Exception $e) {
                                                $barcodeDataUri = '';
                                            }
                                        @endphp
                                        @if(!empty($barcodeDataUri))
                                            <img src="{{ $barcodeDataUri }}" alt="Barkod" style="width: 100%; height: 50px; object-fit: contain;">
                                        @else
                                            <div style="width: 100%; height: 50px; background: #f0f0f0; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-family: monospace; font-size: 12px;">
                                                {{ $labelData['barcode'] }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="barcode-text">{{ $labelData['barcode'] }}</div>
                                    <div class="company-name">RONEX TEKSTIL</div>
                                </div>
                                
                                <div class="qr-section">
                                    <div class="qr-image">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=50x50&data={{ urlencode($labelData['qr_url']) }}" 
                                             alt="QR Kod" 
                                             class="qr-image"
                                             onerror="this.src='{{ route('print.labels.qr', ['text' => $labelData['qr_url'], 'size' => 50]) }}'">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor
            @endif
        @endforeach
    </div>
    
</body>
</html>

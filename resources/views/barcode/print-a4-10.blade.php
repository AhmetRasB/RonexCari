<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Barcode Print</title>
    <style>
        @page { size: A4; margin: 10mm; }
        body { font-family: Arial, sans-serif; }
        .grid { display: grid; grid-template-columns: repeat(2, 1fr); grid-gap: 12mm; }
        .card { border: 1px solid #ddd; padding: 8mm; height: 90mm; display: flex; flex-direction: column; justify-content: space-between; }
        .title { font-weight: 700; text-align: left; }
        .row { display: flex; justify-content: space-between; align-items: center; }
        .qr { width: 35mm; }
        .barcode { width: 70mm; }
        .footer { text-align: center; font-size: 12pt; letter-spacing: 2px; }
        @media print { .no-print { display:none } }
    </style>
</head>
<body>
<div class="no-print" style="margin-bottom:12px;text-align:right">
    <button onclick="window.print()">Yazdır</button>
</div>

<?php $chunks = array_chunk($items, 10); ?>
@foreach($chunks as $chunk)
    <div class="grid" style="page-break-after: always;">
        @foreach($chunk as $product)
            <div class="card">
                <div class="title">
                    {{ ($product->size ? $product->size.' - ' : '') . $product->name }}<br>
                    <span style="font-weight:600">Product Code: {{ $product->sku }}</span>
                </div>
                <div class="row">
                    <div class="qr">
                        @php
                            // Ensure files exist in case of older records
                            app(\App\Services\QrBarcodeService::class)->ensureProductCodes($product);
                        @endphp
                        @if($product->qr_svg_path)
                            <img src="/{{ $product->qr_svg_path }}" style="width:100%" />
                        @endif
                    </div>
                    <div class="barcode">
                        @if($product->barcode_svg_path)
                            <img src="/{{ $product->barcode_svg_path }}" style="width:100%" />
                            <div class="footer">{{ $product->permanent_barcode }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endforeach

</body>
</html>



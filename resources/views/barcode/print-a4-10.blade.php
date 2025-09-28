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
        .barcode { width: 70mm; height: 40px; }
        .footer { text-align: center; font-size: 12pt; letter-spacing: 2px; }
        @media print { .no-print { display:none } }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
</head>
<body>
<div class="no-print" style="margin-bottom:12px;text-align:right">
    <button onclick="window.print()">Yazdır</button>
</div>

<?php $chunks = array_chunk($items, 10); ?>
@foreach($chunks as $chunk)
    <div class="grid" style="page-break-after: always;">
        @foreach($chunk as $item)
            @php
                if (is_array($item)) {
                    // New format with type, item, label, code, barcode
                    $itemObj = $item['item'];
                    $label = $item['label'];
                    $code = $item['code'];
                    $type = $item['type'];
                    $barcode = $item['barcode']; // Kısa barkod
                    
                    if ($type === 'product') {
                        $qrText = route('products.show', $itemObj);
                    } else {
                        $qrText = route('products.series.show', $itemObj);
                    }
                } else {
                    // Old format - backward compatibility
                    if ($item instanceof \App\Models\Product) {
                        $barcode = $item->barcode ?: ('P' . str_pad((string) $item->id, 4, '0', STR_PAD_LEFT));
                        $qrText = route('products.show', $item);
                        $label = ($item->size ? $item->size.' - ' : '') . $item->name;
                        $code = $item->sku ?: $barcode;
                        $type = 'Product';
                    } else {
                        $barcode = $item->barcode ?: ('S' . str_pad((string) $item->id, 4, '0', STR_PAD_LEFT));
                        $qrText = route('products.series.show', $item);
                        $label = $item->name;
                        $code = $item->sku ?: $barcode;
                        $type = 'Series';
                    }
                }
            @endphp
            <div class="card">
                <div class="title">
                    {{ $label }}<br>
                    <span style="font-weight:600">{{ $type }} Code: {{ $code }}</span>
                </div>
                <div class="row">
                    <div class="qr">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($qrText) }}" style="width:100%" />
                    </div>
                    <div class="barcode">
                        <svg class="barcode" data-barcode="{{ $barcode }}" style="width:100%; height:40px;"></svg>
                        <div class="footer">{{ $barcode }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('svg[data-barcode]').forEach(function(svg) {
        const code = svg.getAttribute('data-barcode');
        try { 
            JsBarcode(svg, code, {format:'CODE128', displayValue:false, margin:0, height:40}); 
        } catch(e) {
            console.log('Barcode generation error:', e);
        }
    });
});
</script>

</body>
</html>
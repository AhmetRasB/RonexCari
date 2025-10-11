<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Barcode Print</title>
    <style>
        @page { size: A4; margin: 10mm; }
        body { font-family: Arial, sans-serif; }
        .grid { display: grid; grid-template-columns: repeat(2, 1fr); grid-gap: 12mm; }
        .card { border: 2px solid #333; padding: 8mm; height: 90mm; display: flex; flex-direction: column; justify-content: space-between; }
        .title { font-weight: 700; text-align: left; font-size: 14pt; line-height: 1.3; }
        .subtitle { font-weight: 600; font-size: 11pt; color: #555; margin-top: 2mm; }
        .info { font-size: 10pt; color: #666; margin-top: 1mm; }
        .row { display: flex; justify-content: space-between; align-items: center; margin-top: 3mm; }
        .qr { width: 35mm; }
        .barcode { width: 70mm; height: 40px; }
        .footer { text-align: center; font-size: 11pt; letter-spacing: 1px; font-weight: 600; }
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
                <div>
                    <div class="title">{{ $label }}</div>
                    <div class="subtitle">{{ $type === 'product' ? 'ÜRÜN' : ($type === 'series_main' ? 'DIŞ PAKET' : 'BEDEN') }} - {{ $code }}</div>
                    <div class="info">Seri: 5'li</div>
                </div>
                <div class="row">
                    <div style="flex: 1;">
                        <svg class="barcode" data-barcode="{{ $barcode }}" style="width:100%; height:45px;"></svg>
                        <div class="footer" style="margin-top: 5mm;">{{ $barcode }}</div>
                        <div style="text-align: center; font-size: 12pt; font-weight: 700; margin-top: 2mm;">RONEX TEKSTIL</div>
                    </div>
                    <div class="qr" style="margin-left: 5mm;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($qrText) }}" style="width:100%" />
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
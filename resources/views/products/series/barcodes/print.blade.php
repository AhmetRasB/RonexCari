@php
    $paper = $paper ?? 'A4';
    $type = $type ?? 'both';
    $rows = $rows ?? [];
@endphp
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seri Barkod/QR Etiketleri</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 10px;
        }
        .sheet { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 10px; 
            max-width: 210mm;
            margin: 0 auto;
        }
        .label { 
            border: 1px dashed #ccc; 
            padding: 6px; 
            text-align: center;
            height: 80mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .series-name { 
            font-size: 11px; 
            font-weight: bold; 
            margin-bottom: 2px; 
            line-height: 1.1;
        }
        .variant-info {
            font-size: 10px;
            font-weight: 600;
            color: #333;
            margin-bottom: 2px;
        }
        .meta { 
            font-size: 9px; 
            color: #555; 
            margin-bottom: 4px; 
        }
        .row { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            gap: 6px; 
        }
        .barcode { 
            width: 100%; 
            height: 40px; 
        }
        .qr { 
            width: 64px; 
            height: 64px; 
        }
        .price {
            font-size: 10px;
            font-weight: 600;
            color: #333;
        }
        .sku {
            font-size: 8px;
            color: #666;
            font-family: monospace;
        }
        @media print {
            .no-print { display: none !important; }
            .sheet { gap: 0; }
            .label { border: none; }
            body { padding: 0; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
</head>
<body>
    <div class="no-print" style="margin-bottom:10px; text-align:center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Yazdır</button>
        <button onclick="generatePDF()" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">PDF Olarak İndir</button>
    </div>
    <div class="sheet">
        @foreach($rows as $row)
            @for($i=0;$i<$row['count'];$i++)
                @php
                    $series = $row['series'];
                    $itemType = $row['type'];
                    $identifier = $row['identifier'];
                    
                    // Generate barcode based on series ID or use custom barcode
                    $barcode = $series->barcode ?: ('SER-' . str_pad((string)$series->id, 8, '0', STR_PAD_LEFT));
                    $qrText = route('products.series.show', $series);
                    
                    // Determine label content based on type
                    $labelTitle = $series->name;
                    $variantText = '';
                    
                    if ($itemType === 'color_main') {
                        $variantText = $identifier . ' Ana';
                    } elseif ($itemType === 'size_color') {
                        $variantText = $identifier; // Already formatted as "XL-Kırmızı"
                    } elseif ($itemType === 'size') {
                        $variantText = 'Beden: ' . $identifier;
                    } elseif ($itemType === 'color') {
                        $variantText = 'Renk: ' . $identifier;
                    } else {
                        $variantText = 'Ana Seri';
                    }
                @endphp
                <div class="label">
                    <div>
                        <div class="series-name">{{ \Illuminate\Support\Str::limit($labelTitle, 24) }}</div>
                        <div class="variant-info">{{ $variantText }}</div>
                        @if($series->sku)
                        <div class="sku">{{ $series->sku }}</div>
                        @endif
                    </div>
                    
                    <div>
                        @if($type==='barcode' || $type==='both')
                        <svg class="barcode" data-barcode="{{ $barcode }}"></svg>
                        @endif
                        @if($type==='qr' || $type==='both')
                        <div class="row" style="margin-top:4px;">
                            <img class="qr" src="https://api.qrserver.com/v1/create-qr-code/?size=64x64&data={{ urlencode($qrText) }}" alt="QR">
                            <div style="font-size:8px; text-align:left;">{{ $barcode }}</div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="price">
                        {{ $series->price ? number_format((float)$series->price,2,',','.') . ' ₺' : '' }}
                    </div>
                </div>
            @endfor
        @endforeach
    </div>

    <script>
        function ready(fn){document.readyState!='loading'?fn():document.addEventListener('DOMContentLoaded',fn)}
        ready(function(){
            document.querySelectorAll('svg[data-barcode]').forEach(function(svg){
                const code = svg.getAttribute('data-barcode');
                try { 
                    JsBarcode(svg, code, {format:'CODE128', displayValue:false, margin:0, height:40}); 
                } catch(e) {
                    console.log('Barcode generation error:', e);
                }
            });
        });

        function generatePDF() {
            // Use browser's print to PDF functionality
            window.print();
        }
    </script>
</body>
</html>

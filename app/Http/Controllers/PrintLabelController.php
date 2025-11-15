<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductSeries;

class PrintLabelController extends Controller
{
    /**
     * Generate ZPL for product or series labels (300dpi, 50x30mm).
     * Query params:
     * - type: product|series
     * - id: int
     * - mode (series only): outer|sizes
     */
    public function zpl(Request $request)
    {
        $type = $request->query('type');
        $id = (int) $request->query('id');
        $mode = $request->query('mode', 'outer');
        $count = max(1, (int) $request->query('count', 1));

        if (!in_array($type, ['product', 'series'], true) || $id <= 0) {
            return response()->json(['message' => 'Invalid parameters'], 422);
        }

        if ($type === 'product') {
            $label = $this->buildProductZpl($id, $count);
        } else {
            if ($mode === 'full') {
                $label = $this->buildSeriesZplFull($id, $count);
            } else {
                $label = $this->buildSeriesZpl($id, $mode, $count);
            }
        }

        if ($label === null) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return response($label, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="etiket.zpl"',
        ]);
    }

    /**
     * Generate ZPL for a specific color variant of a series.
     * Query params:
     * - id: series id
     * - color: color name
     * - mode: outer|sizes
     * - count: number of labels
     */
    public function zplByColor(Request $request)
    {
        $id = (int) $request->query('id');
        $color = $request->query('color');
        $mode = $request->query('mode', 'outer');
        $count = max(1, (int) $request->query('count', 1));

        if ($id <= 0 || empty($color)) {
            return response()->json(['message' => 'Invalid parameters'], 422);
        }

        $label = $this->buildSeriesZplByColor($id, $color, $mode, $count);

        if ($label === null) {
            return response()->json(['message' => 'Item or color not found'], 404);
        }

        return response($label, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="etiket.zpl"',
        ]);
    }

    /**
     * Preview label as PNG via Labelary for a specific color variant.
     * Query: id, color, mode
     */
    public function previewPngByColor(Request $request)
    {
        $id = (int) $request->query('id');
        $color = $request->query('color');
        $mode = $request->query('mode', 'outer');

        if ($id <= 0 || empty($color)) {
            return response()->json(['message' => 'Invalid parameters'], 422);
        }

        $zpl = $this->buildSeriesZplByColor($id, $color, $mode);
        if ($zpl === null) {
            return response()->json(['message' => 'Item or color not found'], 404);
        }

        // Use Laravel HTTP client instead of cURL (Labelary API documentation example)
        // According to Labelary docs: POST with application/x-www-form-urlencoded, body contains raw ZPL
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'image/png',
                ])
                ->withOptions([
                    'verify' => false, // Dev ortamında SSL doğrulamasını atla
                ])
                ->withBody($zpl, 'application/x-www-form-urlencoded')
                ->post('https://api.labelary.com/v1/printers/12dpmm/labels/1.97x1.18/0/');

            if ($response->successful() && $response->body()) {
                return response($response->body(), 200, [
                    'Content-Type' => 'image/png',
                ]);
            }

            return response()->json([
                'message' => 'Labelary render failed',
                'status' => $response->status(),
                'error' => $response->body(),
            ], 502);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Labelary request failed',
                'error' => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Preview label as PNG via Labelary (server-side proxy to avoid CORS).
     * Query: type, id, mode (series only)
     */
    public function previewPng(Request $request)
    {
        $type = $request->query('type');
        $id = (int) $request->query('id');
        $mode = $request->query('mode', 'outer');

        if (!in_array($type, ['product', 'series'], true) || $id <= 0) {
            return response()->json(['message' => 'Invalid parameters'], 422);
        }

        if ($type === 'product') {
            $zpl = $this->buildProductZpl($id);
        } else {
            $zpl = $this->buildSeriesZpl($id, $mode);
        }
        if ($zpl === null) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        // Use Laravel HTTP client instead of cURL (Labelary API documentation example)
        // According to Labelary docs: POST with application/x-www-form-urlencoded, body contains raw ZPL
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'image/png',
                ])
                ->withOptions([
                    'verify' => false, // Dev ortamında SSL doğrulamasını atla
                ])
                ->withBody($zpl, 'application/x-www-form-urlencoded')
                ->post('https://api.labelary.com/v1/printers/12dpmm/labels/1.97x1.18/0/');

            if ($response->successful() && $response->body()) {
                return response($response->body(), 200, [
                    'Content-Type' => 'image/png',
                ]);
            }

            return response()->json([
                'message' => 'Labelary render failed',
                'status' => $response->status(),
                'error' => $response->body(),
            ], 502);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Labelary request failed',
                'error' => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Export CSV for BarTender (.btw) workflows.
     * Columns (product): type,category,name,color,size,barcode
     * Columns (series outer): type,category,name,series_size,colors,sizes,barcode
     * Columns (series sizes): type,category,name,size,color,barcode
     */
    public function csv(Request $request)
    {
        $type = $request->query('type');
        $id = (int) $request->query('id');
        $mode = $request->query('mode', 'outer');

        if (!in_array($type, ['product', 'series'], true) || $id <= 0) {
            return response()->json(['message' => 'Invalid parameters'], 422);
        }

        $lines = [];
        if ($type === 'product') {
            $p = Product::with('colorVariants')->find($id);
            if (!$p) return response()->json(['message' => 'Item not found'], 404);
            
            // Eğer renk varyantları varsa, her renk için ayrı satır
            if ($p->colorVariants && $p->colorVariants->count() > 0) {
                foreach ($p->colorVariants as $colorVariant) {
                    $lines[] = [
                        'product',
                        $this->sanitize($p->category ?? ''),
                        $this->sanitize($p->name ?? ''),
                        $this->sanitize($colorVariant->color ?? ''),
                        $this->sanitize($p->size ?? ''),
                        $this->sanitize($p->barcode ?: ($p->sku ?: ('P' . str_pad((string)$p->id, 4, '0', STR_PAD_LEFT)))),
                        $colorVariant->stock_quantity ?? 0,
                    ];
                }
            } else {
                $lines[] = [
                    'product',
                    $this->sanitize($p->category ?? ''),
                    $this->sanitize($p->name ?? ''),
                    $this->sanitize($p->color ?? ''),
                    $this->sanitize($p->size ?? ''),
                    $this->sanitize($p->barcode ?: ($p->sku ?: ('P' . str_pad((string)$p->id, 4, '0', STR_PAD_LEFT)))),
                    $p->stock_quantity ?? 0,
                ];
            }
            $header = ['type','category','name','color','size','barcode','stock'];
        } else {
            $s = ProductSeries::with(['seriesItems','colorVariants'])->find($id);
            if (!$s) return response()->json(['message' => 'Item not found'], 404);
            $barcode = $this->sanitize($s->barcode ?: ($s->sku ?: ('S' . str_pad((string)$s->id, 4, '0', STR_PAD_LEFT))));
            if ($mode === 'sizes') {
                $header = ['type','category','name','size','color','series_size','barcode'];
                $colors = $s->colorVariants->pluck('color')->filter()->values()->all();
                $sizes = $s->seriesItems->pluck('size')->filter()->values()->all();
                
                // Renk varyantı varsa: Her renk x Her beden
                if (count($colors) > 0) {
                    foreach ($colors as $color) {
                        foreach ($sizes as $size) {
                            $lines[] = [
                                'series_size',
                                $this->sanitize($s->category ?? ''),
                                $this->sanitize($s->name ?? ''),
                                $this->sanitize((string)$size),
                                $this->sanitize($color),
                                (int) ($s->series_size ?? 0),
                                $barcode,
                            ];
                        }
                    }
                } else {
                    // Renk yoksa sadece bedenler
                    foreach ($sizes as $size) {
                        $lines[] = [
                            'series_size',
                            $this->sanitize($s->category ?? ''),
                            $this->sanitize($s->name ?? ''),
                            $this->sanitize((string)$size),
                            '',
                            (int) ($s->series_size ?? 0),
                            $barcode,
                        ];
                    }
                }
            } else {
                $header = ['type','category','name','series_size','colors','sizes','barcode','year'];
                $sizesCsv = $this->sanitize(implode(' ', $s->seriesItems->pluck('size')->filter()->values()->all()));
                $colorsCsv = $this->sanitize(implode(', ', $s->colorVariants->pluck('color')->filter()->values()->all()));
                $lines[] = [
                    'series_outer',
                    $this->sanitize($s->category ?? ''),
                    $this->sanitize($s->name ?? ''),
                    (int) ($s->series_size ?? 0),
                    $colorsCsv,
                    $sizesCsv,
                    $barcode,
                    date('Y'),
                ];
            }
        }

        $fh = fopen('php://temp', 'w+');
        fputcsv($fh, $header);
        foreach ($lines as $row) {
            fputcsv($fh, $row);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="labels.csv"',
        ]);
    }

    /**
     * Export BTXML Script for BarTender integrations.
     * Clients can post this to BarTender Integration service to print.
     */
    public function btxml(Request $request)
    {
        $type = $request->query('type');
        $id = (int) $request->query('id');
        $mode = $request->query('mode', 'outer');

        if (!in_array($type, ['product', 'series'], true) || $id <= 0) {
            return response()->json(['message' => 'Invalid parameters'], 422);
        }

        // Data fields will align with CSV headers for simplicity
        $items = [];
        if ($type === 'product') {
            $p = Product::with('colorVariants')->find($id);
            if (!$p) return response()->json(['message' => 'Item not found'], 404);
            
            // Eğer renk varyantları varsa, her renk için ayrı item
            if ($p->colorVariants && $p->colorVariants->count() > 0) {
                foreach ($p->colorVariants as $colorVariant) {
                    $items[] = [
                        'type' => 'product',
                        'category' => $this->sanitize($p->category ?? ''),
                        'name' => $this->sanitize($p->name ?? ''),
                        'color' => $this->sanitize($colorVariant->color ?? ''),
                        'size' => $this->sanitize($p->size ?? ''),
                        'barcode' => $this->sanitize($p->barcode ?: ($p->sku ?: ('P' . str_pad((string)$p->id, 4, '0', STR_PAD_LEFT)))),
                        'stock' => $colorVariant->stock_quantity ?? 0,
                        'year' => date('Y'),
                    ];
                }
            } else {
                $items[] = [
                    'type' => 'product',
                    'category' => $this->sanitize($p->category ?? ''),
                    'name' => $this->sanitize($p->name ?? ''),
                    'color' => $this->sanitize($p->color ?? ''),
                    'size' => $this->sanitize($p->size ?? ''),
                    'barcode' => $this->sanitize($p->barcode ?: ($p->sku ?: ('P' . str_pad((string)$p->id, 4, '0', STR_PAD_LEFT)))),
                    'stock' => $p->stock_quantity ?? 0,
                    'year' => date('Y'),
                ];
            }
        } else {
            $s = ProductSeries::with(['seriesItems','colorVariants'])->find($id);
            if (!$s) return response()->json(['message' => 'Item not found'], 404);
            $barcode = $this->sanitize($s->barcode ?: ($s->sku ?: ('S' . str_pad((string)$s->id, 4, '0', STR_PAD_LEFT))));
            if ($mode === 'sizes') {
                $colors = $s->colorVariants->pluck('color')->filter()->values()->all();
                $sizes = $s->seriesItems->pluck('size')->filter()->values()->all();
                
                // Renk varyantı varsa: Her renk x Her beden
                if (count($colors) > 0) {
                    foreach ($colors as $color) {
                        foreach ($sizes as $size) {
                            $items[] = [
                                'type' => 'series_size',
                                'category' => $this->sanitize($s->category ?? ''),
                                'name' => $this->sanitize($s->name ?? ''),
                                'size' => $this->sanitize((string)$size),
                                'color' => $this->sanitize($color),
                                'series_size' => (int) ($s->series_size ?? 0),
                                'barcode' => $barcode,
                                'year' => date('Y'),
                            ];
                        }
                    }
                } else {
                    // Renk yoksa sadece bedenler
                    foreach ($sizes as $size) {
                        $items[] = [
                            'type' => 'series_size',
                            'category' => $this->sanitize($s->category ?? ''),
                            'name' => $this->sanitize($s->name ?? ''),
                            'size' => $this->sanitize((string)$size),
                            'color' => '',
                            'series_size' => (int) ($s->series_size ?? 0),
                            'barcode' => $barcode,
                            'year' => date('Y'),
                        ];
                    }
                }
            } else {
                $items[] = [
                    'type' => 'series_outer',
                    'category' => $this->sanitize($s->category ?? ''),
                    'name' => $this->sanitize($s->name ?? ''),
                    'series_size' => (int) ($s->series_size ?? 0),
                    'colors' => $this->sanitize(implode(', ', $s->colorVariants->pluck('color')->filter()->values()->all())),
                    'sizes' => $this->sanitize(implode(' ', $s->seriesItems->pluck('size')->filter()->values()->all())),
                    'barcode' => $barcode,
                    'year' => date('Y'),
                ];
            }
        }

        $xml = $this->renderBtxml($items);

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="labels.xml"',
        ]);
    }

    private function renderBtxml(array $items): string
    {
        // Minimal BTXML Script – the user will point Integration to a .btw that maps these NamedDataSources
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $root = $doc->createElement('XMLScript');
        $root->setAttribute('Version', '2.0');
        $doc->appendChild($root);

        foreach ($items as $data) {
            $cmd = $doc->createElement('Command');
            $cmd->setAttribute('Name', 'Job1');
            $print = $doc->createElement('Print');
            $format = $doc->createElement('Format');
            // Client kendi .btw yolunu Integration tarafında set edecek; burada placeholder bırakıyoruz.
            $format->appendChild($doc->createTextNode('C:\\Path\\To\\YourTemplate.btw'));
            $print->appendChild($format);

            $named = $doc->createElement('NamedSubStrings');
            foreach ($data as $key => $value) {
                $sub = $doc->createElement('NamedSubString');
                $sub->setAttribute('Name', $key);
                $val = $doc->createElement('Value');
                $val->appendChild($doc->createTextNode((string)$value));
                $sub->appendChild($val);
                $named->appendChild($sub);
            }

            $print->appendChild($named);
            $cmd->appendChild($print);
            $root->appendChild($cmd);
        }

        $doc->formatOutput = true;
        return $doc->saveXML();
    }

    private function sanitize(?string $value): string
    {
        if ($value === null) return '';
        // UTF-8 desteği: Türkçe karakterleri koru, sadece ZPL kontrol karakterlerini kaçır
        return preg_replace('/[\^~]/', '-', $value);
    }

    private function buildProductZpl(int $productId, int $count = 1): ?string
    {
        $product = Product::with('colorVariants')->find($productId);
        if (!$product) return null;

        $blocks = [];
        
        // Eğer renk varyantları varsa, her renk için ayrı etiket
        if ($product->colorVariants && $product->colorVariants->count() > 0) {
            foreach ($product->colorVariants as $colorVariant) {
                $category = $this->sanitize($product->category ?? '');
                $name = $this->sanitize($product->name ?? '');
                $color = $this->sanitize($colorVariant->color ?? '');
                $size = $this->sanitize($product->size ?? '');
                $barcode = $this->sanitize($product->barcode ?: ($product->sku ?: ('P' . str_pad((string)$product->id, 4, '0', STR_PAD_LEFT))));
                $qr = url('/products/' . $product->id);
                $stock = $colorVariant->stock_quantity ?? 0;

                // Geliştirilmiş etiket formatı - Sadece bedenler, büyük barkod
                $one = "^XA\n" .
                       "^CI28\n" .
                       "^PW500\n" .
                       "^LL300\n" .
                       "^LH10,10\n" .
                       "^FO10,10^GB480,280,2^FS\n" .
                       "^FO20,20^A0N,24,24^FD{$category}^FS\n" .
                       "^FO20,48^A0N,28,28^FD{$name}^FS\n" .
                       "^FO360,15^BQN,2,3^FDLA,{$qr}^FS\n" .
                       ($size !== '' ? "^FO20,80^A0N,26,26^FDBEDEN: {$size}^FS\n" : '') .
                       "^FO20," . ($size !== '' ? '110' : '80') . "^A0N,22,22^FDSeri: 5'li^FS\n" .
                       "^FO20," . ($size !== '' ? '138' : '108') . "^A0N,20,20^FD{$barcode}^FS\n" .
                       "^BY4,2,100\n" .
                       "^FO20," . ($size !== '' ? '165' : '135') . "^BCN,100,N,N,N^FD{$barcode}^FS\n" .
                       "^XZ\n";
                $blocks[] = str_repeat($one, max(1, $count));
            }
        } else {
            // Renk varyantı yoksa normal etiket
            $category = $this->sanitize($product->category ?? '');
            $name = $this->sanitize($product->name ?? '');
            $color = $this->sanitize($product->color ?? '');
            $size = $this->sanitize($product->size ?? '');
            $barcode = $this->sanitize($product->barcode ?: ($product->sku ?: ('P' . str_pad((string)$product->id, 4, '0', STR_PAD_LEFT))));
            $qr = url('/products/' . $product->id);
            $stock = $product->stock_quantity ?? 0;

            $one = "^XA\n" .
                   "^CI28\n" .
                   "^PW500\n" .
                   "^LL300\n" .
                   "^LH10,10\n" .
                   "^FO10,10^GB480,280,2^FS\n" .
                   "^FO20,20^A0N,24,24^FD{$category}^FS\n" .
                   "^FO20,48^A0N,28,28^FD{$name}^FS\n" .
                   "^FO360,15^BQN,2,3^FDLA,{$qr}^FS\n" .
                   ($size !== '' ? "^FO20,80^A0N,26,26^FDBEDEN: {$size}^FS\n" : '') .
                   "^FO20," . ($size !== '' ? '110' : '80') . "^A0N,22,22^FDSeri: 5'li^FS\n" .
                   "^FO20," . ($size !== '' ? '138' : '108') . "^A0N,20,20^FD{$barcode}^FS\n" .
                   "^BY4,2,100\n" .
                   "^FO20," . ($size !== '' ? '165' : '135') . "^BCN,100,N,N,N^FD{$barcode}^FS\n" .
                   "^XZ\n";
            $blocks[] = str_repeat($one, max(1, $count));
        }
        
        return implode('', $blocks);
    }

    private function buildSeriesZpl(int $seriesId, string $mode, int $count = 1): ?string
    {
        $series = ProductSeries::with(['seriesItems', 'colorVariants'])->find($seriesId);
        if (!$series) return null;

        $category = $this->sanitize($series->category ?? '');
        $seriesSize = (int) ($series->series_size ?? 0);
        // Seri boyutunu hesapla: kayıtlı değer; yoksa içeriğin toplam miktarı; o da yoksa beden sayısı
        $seriesItemsSum = $series->seriesItems->sum('quantity_per_series');
        $calculatedSize = $seriesSize ?: ($seriesItemsSum ?: $series->seriesItems->count());
        // Sadece ürün adı (seri boyutu ekleme)
        $name = $this->sanitize($series->name ?? '');
        $barcode = $this->sanitize($series->barcode ?: ($series->sku ?: ('S' . str_pad((string)$series->id, 4, '0', STR_PAD_LEFT))));
        
        // TÜM bedenler - tekrar edenlerle birlikte (unique değil!)
        $allSizes = $series->seriesItems->pluck('size')->filter()->all();
        
        \Log::info('ZPL - Series sizes', [
            'series_id' => $series->id,
            'series_items_count' => $series->seriesItems->count(),
            'series_items' => $series->seriesItems->map(function($item) {
                return ['id' => $item->id, 'size' => $item->size, 'quantity' => $item->quantity_per_series];
            })->toArray(),
            'all_sizes' => $allSizes,
            'all_sizes_count' => count($allSizes)
        ]);
        
        // Renkleri al - TÜM renkleri al, aktif/pasif fark etmez
        $colors = $series->colorVariants->pluck('color')->filter()->values()->all();
        
        // Eğer renk yoksa, colorVariants ilişkisini tekrar yükle
        if (empty($colors) && $series->relationLoaded('colorVariants')) {
            $series->load('colorVariants');
            $colors = $series->colorVariants->pluck('color')->filter()->values()->all();
        }
        
        $qrSeries = url('/products/series/' . $series->id);

        // Dış pakette TÜM bedenler gösterilecek (tekrarlılar dahil!)
        $sizesCsv = $this->sanitize(implode(' ', $allSizes));
        $colorsCsv = $this->sanitize(implode(', ', $colors));

        if ($mode === 'sizes') {
            // Her renk için ayrı beden etiketleri üret
            $blocks = [];
            
            \Log::info('ZPL Sizes mode - Starting', [
                'series_id' => $series->id,
                'series_name' => $series->name,
                'colors_count' => count($colors),
                'colors' => $colors,
                'sizes_count' => count($allSizes),
                'sizes' => $allSizes,
                'count' => $count
            ]);
            
            // Eğer renk varsa, her renk için ayrı beden etiketleri üret
            if (count($colors) > 0) {
                foreach ($colors as $colorIndex => $color) {
                    $colorSan = $this->sanitize($color);
                    \Log::info('ZPL Sizes mode - Processing color', [
                        'color_index' => $colorIndex,
                        'color' => $color,
                        'sizes_count' => count($allSizes)
                    ]);
                    
                    // TÜM bedenler (tekrarlı olanlar dahil) - her renk için
                    foreach ($allSizes as $sizeIndex => $size) {
                        $sizeSan = $this->sanitize((string)$size);
                        $one = "^XA\n" .
                               "^CI28\n" .
                               "^PW500\n" .
                               "^LL300\n" .
                               "^LH10,10\n" .
                               "^FO10,10^GB480,280,2^FS\n" .
                               "^FO20,20^A0N,28,28^FD{$name}^FS\n" .
                               "^FO20,56^A0N,24,24^FD{$colorSan}^FS\n" .
                               "^FO20,88^A0N,26,26^FD{$sizeSan}^FS\n" .
                               "^FO370,10^BQN,2,2^FDLA,{$qrSeries}^FS\n" .
                               "^FO20,122^A0N,20,20^FD{$barcode}^FS\n" .
                               "^BY4,2,90\n" .
                               "^FO20,150^BCN,90,N,N,N^FD{$barcode}^FS\n" .
                               "^XZ\n";
                        $blocks[] = str_repeat($one, max(1, $count));
                        \Log::info('ZPL Sizes mode - Added label', [
                            'color' => $color,
                            'size' => $size,
                            'blocks_count' => count($blocks)
                        ]);
                    }
                }
            } else {
                // Renk yoksa normal beden etiketleri
                foreach ($allSizes as $size) {
                    $sizeSan = $this->sanitize((string)$size);
                    $one = "^XA\n" .
                           "^CI28\n" .
                           "^PW500\n" .
                           "^LL300\n" .
                           "^LH10,10\n" .
                           "^FO10,10^GB480,280,2^FS\n" .
                           "^FO20,20^A0N,28,28^FD{$name}^FS\n" .
                           "^FO20,56^A0N,26,26^FD{$sizeSan}^FS\n" .
                           "^FO20,90^A0N,20,20^FD{$barcode}^FS\n" .
                           "^FO370,16^BQN,2,2^FDLA,{$qrSeries}^FS\n" .
                           "^BY4,2,90\n" .
                           "^FO20,118^BCN,90,N,N,N^FD{$barcode}^FS\n" .
                           "^XZ\n";
                    $blocks[] = str_repeat($one, max(1, $count));
                }
            }
            
            \Log::info('ZPL Sizes mode - Final result', [
                'series_id' => $series->id,
                'blocks_count' => count($blocks),
                'result_length' => strlen(implode('', $blocks))
            ]);
            
            return implode('', $blocks);
        }

        // DIŞ paket etiketi (OUTER mode) - Her renk için ayrı dış etiket

        $blocks = [];
        // Renkleri tekrar al (outer modunda) - TÜM renkleri al, aktif/pasif fark etmez
        $colors = $series->colorVariants->pluck('color')->filter()->values()->all();
        
        \Log::info('ZPL Outer mode - Colors found', [
            'series_id' => $series->id,
            'series_name' => $series->name,
            'color_variants_count' => $series->colorVariants->count(),
            'color_variants' => $series->colorVariants->map(function($cv) {
                return ['id' => $cv->id, 'color' => $cv->color, 'is_active' => $cv->is_active];
            })->toArray(),
            'colors_count' => count($colors),
            'colors' => $colors
        ]);
        
        // Eğer renk varsa, her renk için ayrı dış etiket üret
        if (count($colors) > 0) {
            foreach ($colors as $colorIndex => $color) {
                $colorSan = $this->sanitize($color);
                $one = "^XA\n" .    
                       "^CI28\n" .
                       "^PW500\n" .
                       "^LL300\n" .
                       "^LH10,10\n" .
                       "^FO10,10^GB480,280,2^FS\n" .
                       "^FO20,20^A0N,30,30^FD{$name}^FS\n" .
                       "^FO20,58^A0N,24,24^FD{$colorSan}^FS\n" .
                       ($sizesCsv !== '' ? "^FO20,90^A0N,22,22^FD{$sizesCsv}^FS\n" : '') .
                       "^FO20,120^A0N,20,20^FD{$barcode}^FS\n" .
                       "^FO360,15^BQN,2,3^FDLA,{$qrSeries}^FS\n" .
                       "^BY4,2,90\n" .
                       "^FO20,148^BCN,90,N,N,N^FD{$barcode}^FS\n" .
                       "^XZ\n";
                $blocks[] = str_repeat($one, max(1, $count));
                \Log::info('ZPL Outer mode - Added label for color', [
                    'series_id' => $series->id,
                    'color_index' => $colorIndex,
                    'color' => $color,
                    'blocks_count' => count($blocks)
                ]);
            }
        } else {
            // Renk yoksa normal dış etiket
        $one = "^XA\n" .    
               "^CI28\n" .
               "^PW500\n" .
               "^LL300\n" .
               "^LH10,10\n" .
               "^FO10,10^GB480,280,2^FS\n" .
               "^FO20,20^A0N,30,30^FD{$name}^FS\n" .
               ($sizesCsv !== '' ? "^FO20,58^A0N,22,22^FD{$sizesCsv}^FS\n" : '') .
               "^FO20,88^A0N,20,20^FD{$barcode}^FS\n" .
               "^FO360,15^BQN,2,3^FDLA,{$qrSeries}^FS\n" .
               "^BY4,2,90\n" .
               "^FO20,116^BCN,90,N,N,N^FD{$barcode}^FS\n" .
               "^XZ\n";
            $blocks[] = str_repeat($one, max(1, $count));
        }
        
        $result = implode('', $blocks);
        \Log::info('ZPL Outer mode - Final result', [
            'series_id' => $series->id,
            'blocks_count' => count($blocks),
            'result_length' => strlen($result),
            'result_preview' => substr($result, 0, 200) . '...'
        ]);
        
        return $result;
    }

    private function buildSeriesZplFull(int $seriesId, int $packages = 1): ?string
    {
        $root = ProductSeries::with(['seriesItems', 'colorVariants', 'children.colorVariants', 'children.seriesItems'])
            ->find($seriesId);
        if (!$root) return null;

        // Build group: parent + children if exists, else children of root, else fallback same barcode
        $group = collect();
        if ($root->parent_series_id) {
            $parent = ProductSeries::with(['children.colorVariants','children.seriesItems','seriesItems','colorVariants'])
                ->find($root->parent_series_id);
            if ($parent) {
                $group = $parent->children->push($parent);
            }
        } else {
            if ($root->children && $root->children->count() > 0) {
                $group = $root->children->push($root);
            } else {
                $sameBarcode = ProductSeries::with(['seriesItems','colorVariants'])
                    ->where('barcode', $root->barcode)
                    ->get();
                $group = $sameBarcode->count() > 0 ? $sameBarcode : collect([$root]);
            }
        }

        // Ensure unique series and order by series_size
        $group = $group->unique('id')->sortBy(function($s){ return (int) ($s->series_size ?? 0); });

        $blocks = [];
        foreach ($group as $series) {
            // Sadece ürün adı (seri boyutu ve kategori ekleme)
            $name = $this->sanitize($series->name ?? '');
            $barcode = $this->sanitize($series->barcode ?: ($series->sku ?: ('S' . str_pad((string)$series->id, 4, '0', STR_PAD_LEFT))));
            $qrSeries = url('/products/series/' . $series->id);
            
            // TÜM bedenler - tekrar edenlerle birlikte
            $allSizes = $series->seriesItems->pluck('size')->filter()->all();
            $sizesCsv = $this->sanitize(implode(' ', $allSizes));
            
            // Renkleri al
            $colors = $series->colorVariants->pluck('color')->filter()->values()->all();
            
            // Eğer renk varsa, her renk için ayrı dış etiket + beden etiketleri + renk etiketi
            if (count($colors) > 0) {
                foreach ($colors as $color) {
                    $colorSan = $this->sanitize($color);
                    
                    // 1) Dış etiket (her renk için ayrı)
                    $outerOne = "^XA\n" .    
                               "^CI28\n" .
                               "^PW500\n" .
                               "^LL300\n" .
                               "^LH10,10\n" .
                               "^FO10,10^GB480,280,2^FS\n" .
                               "^FO20,20^A0N,30,30^FD{$name}^FS\n" .
                               "^FO20,58^A0N,24,24^FD{$colorSan}^FS\n" .
                               ($sizesCsv !== '' ? "^FO20,90^A0N,22,22^FD{$sizesCsv}^FS\n" : '') .
                               "^FO20,120^A0N,20,20^FD{$barcode}^FS\n" .
                               "^FO360,15^BQN,2,3^FDLA,{$qrSeries}^FS\n" .
                               "^BY4,2,90\n" .
                               "^FO20,148^BCN,90,N,N,N^FD{$barcode}^FS\n" .
                               "^XZ\n";
                    $blocks[] = str_repeat($outerOne, max(1, $packages));
                    
                    // 2) Beden etiketleri (her renk için)
                    foreach ($allSizes as $size) {
                        $sizeSan = $this->sanitize((string)$size);
                        $sizeOne = "^XA\n" .
                                   "^CI28\n" .
                                   "^PW500\n" .
                                   "^LL300\n" .
                                   "^LH10,10\n" .
                                   "^FO10,10^GB480,280,2^FS\n" .
                                   "^FO20,20^A0N,28,28^FD{$name}^FS\n" .
                                   "^FO20,56^A0N,24,24^FD{$colorSan}^FS\n" .
                                   "^FO20,88^A0N,26,26^FD{$sizeSan}^FS\n" .
                                   "^FO20,122^A0N,20,20^FD{$barcode}^FS\n" .
                                   "^FO370,16^BQN,2,2^FDLA,{$qrSeries}^FS\n" .
                                   "^BY4,2,90\n" .
                                   "^FO20,150^BCN,90,N,N,N^FD{$barcode}^FS\n" .
                                   "^XZ\n";
                        $blocks[] = str_repeat($sizeOne, max(1, $packages));
                    }
                    
                    // 3) Renk etiketi (her renk için)
                    $colorOne = "^XA\n" .
                               "^CI28\n" .
                               "^PW500\n" .
                               "^LL300\n" .
                               "^LH10,10\n" .
                               "^FO10,10^GB480,280,2^FS\n" .
                               "^FO20,20^A0N,28,28^FD{$name}^FS\n" .
                               "^FO20,56^A0N,24,24^FD{$colorSan}^FS\n" .
                               "^FO20,88^A0N,20,20^FD{$barcode}^FS\n" .
                               "^FO370,16^BQN,2,2^FDLA,{$qrSeries}^FS\n" .
                               "^BY4,2,90\n" .
                               "^FO20,116^BCN,90,N,N,N^FD{$barcode}^FS\n" .
                               "^XZ\n";
                    $blocks[] = str_repeat($colorOne, max(1, $packages));
                }
            } else {
                // Renk yoksa: dış etiket + beden etiketleri
                // 1) Dış etiket
                $outerOne = "^XA\n" .    
                           "^CI28\n" .
                           "^PW500\n" .
                           "^LL300\n" .
                           "^LH10,10\n" .
                           "^FO10,10^GB480,280,2^FS\n" .
                           "^FO20,20^A0N,30,30^FD{$name}^FS\n" .
                           ($sizesCsv !== '' ? "^FO20,58^A0N,22,22^FD{$sizesCsv}^FS\n" : '') .
                           "^FO20,88^A0N,20,20^FD{$barcode}^FS\n" .
                           "^FO360,15^BQN,2,3^FDLA,{$qrSeries}^FS\n" .
                           "^BY4,2,90\n" .
                           "^FO20,116^BCN,90,N,N,N^FD{$barcode}^FS\n" .
                           "^XZ\n";
                $blocks[] = str_repeat($outerOne, max(1, $packages));
                
                // 2) Beden etiketleri
                foreach ($allSizes as $size) {
                    $sizeSan = $this->sanitize((string)$size);
                    $sizeOne = "^XA\n" .
                               "^CI28\n" .
                               "^PW500\n" .
                               "^LL300\n" .
                               "^LH10,10\n" .
                               "^FO10,10^GB480,280,2^FS\n" .
                               "^FO20,20^A0N,28,28^FD{$name}^FS\n" .
                               "^FO20,56^A0N,26,26^FD{$sizeSan}^FS\n" .
                               "^FO20,90^A0N,20,20^FD{$barcode}^FS\n" .
                               "^FO370,16^BQN,2,2^FDLA,{$qrSeries}^FS\n" .
                               "^BY4,2,90\n" .
                               "^FO20,118^BCN,90,N,N,N^FD{$barcode}^FS\n" .
                               "^XZ\n";
                    $blocks[] = str_repeat($sizeOne, max(1, $packages));
                }
            }
        }

        return implode('', $blocks);
    }

    /**
     * Build ZPL for a single size label (one color, one size)
     */
    private function buildSingleSizeZpl(int $seriesId, string $color, string $size): ?string
    {
        $series = ProductSeries::with(['seriesItems', 'colorVariants'])->find($seriesId);
        if (!$series) return null;

        $colorVariant = $series->colorVariants->firstWhere('color', $color);
        if (!$colorVariant) return null;

        $name = $this->sanitize($series->name ?? '');
        $colorSan = $this->sanitize($color);
        $sizeSan = $this->sanitize((string)$size);
        $barcode = $this->sanitize($series->barcode ?: ($series->sku ?: ('S' . str_pad((string)$series->id, 4, '0', STR_PAD_LEFT))));
        $qrSeries = url('/products/series/' . $series->id);

        $one = "^XA\n" .
               "^CI28\n" .
               "^PW500\n" .
               "^LL300\n" .
               "^LH10,10\n" .
               "^FO10,10^GB480,280,2^FS\n" .
               "^FO20,20^A0N,28,28^FD{$name}^FS\n" .
               "^FO20,56^A0N,24,24^FD{$colorSan}^FS\n" .
               "^FO20,88^A0N,26,26^FD{$sizeSan}^FS\n" .
               "^FO370,10^BQN,2,2^FDLA,{$qrSeries}^FS\n" .
               "^FO20,122^A0N,20,20^FD{$barcode}^FS\n" .
               "^BY4,2,90\n" .
               "^FO20,150^BCN,90,N,N,N^FD{$barcode}^FS\n" .
               "^XZ\n";
        
        return $one;
    }

    /**
     * Build ZPL for a specific color variant of a series.
     */
    private function buildSeriesZplByColor(int $seriesId, string $color, string $mode, int $count = 1): ?string
    {
        $series = ProductSeries::with(['seriesItems', 'colorVariants'])->find($seriesId);
        if (!$series) return null;

        // Find the specific color variant
        $colorVariant = $series->colorVariants->firstWhere('color', $color);
        if (!$colorVariant) return null;

        $name = $this->sanitize($series->name ?? '');
        $colorSan = $this->sanitize($color);
        $barcode = $this->sanitize($series->barcode ?: ($series->sku ?: ('S' . str_pad((string)$series->id, 4, '0', STR_PAD_LEFT))));
        $qrSeries = url('/products/series/' . $series->id);
        
        $allSizes = $series->seriesItems->pluck('size')->filter()->all();
        $sizesCsv = $this->sanitize(implode(' ', $allSizes));
        
        \Log::info('ZPL ByColor - Series sizes', [
            'series_id' => $series->id,
            'color' => $color,
            'mode' => $mode,
            'series_items_count' => $series->seriesItems->count(),
            'series_items' => $series->seriesItems->map(function($item) {
                return ['id' => $item->id, 'size' => $item->size, 'quantity' => $item->quantity_per_series];
            })->toArray(),
            'all_sizes' => $allSizes,
            'all_sizes_count' => count($allSizes)
        ]);

        if ($mode === 'sizes') {
            // Beden etiketleri (sadece bu renk için)
            $blocks = [];
            foreach ($allSizes as $size) {
                $sizeSan = $this->sanitize((string)$size);
                $one = "^XA\n" .
                       "^CI28\n" .
                       "^PW500\n" .
                       "^LL300\n" .
                       "^LH10,10\n" .
                       "^FO10,10^GB480,280,2^FS\n" .
                       "^FO20,20^A0N,28,28^FD{$name}^FS\n" .
                       "^FO20,56^A0N,24,24^FD{$colorSan}^FS\n" .
                       "^FO20,88^A0N,26,26^FD{$sizeSan}^FS\n" .
                       "^FO370,10^BQN,2,2^FDLA,{$qrSeries}^FS\n" .
                       "^FO20,122^A0N,20,20^FD{$barcode}^FS\n" .
                       "^BY4,2,90\n" .
                       "^FO20,150^BCN,90,N,N,N^FD{$barcode}^FS\n" .
                       "^XZ\n";
                $blocks[] = str_repeat($one, max(1, $count));
            }
            return implode('', $blocks);
        } elseif ($mode === 'full') {
            // Full modu: Dış etiket + Beden etiketleri + Renk etiketi (sadece bu renk için)
            $blocks = [];
            
            // 1) Dış etiket (bu renk için)
            $outerOne = "^XA\n" .    
                       "^CI28\n" .
                       "^PW500\n" .
                       "^LL300\n" .
                       "^LH10,10\n" .
                       "^FO10,10^GB480,280,2^FS\n" .
                       "^FO20,20^A0N,30,30^FD{$name}^FS\n" .
                       "^FO20,58^A0N,24,24^FD{$colorSan}^FS\n" .
                       ($sizesCsv !== '' ? "^FO20,90^A0N,22,22^FD{$sizesCsv}^FS\n" : '') .
                       "^FO20,120^A0N,20,20^FD{$barcode}^FS\n" .
                       "^FO360,15^BQN,2,3^FDLA,{$qrSeries}^FS\n" .
                       "^BY4,2,90\n" .
                       "^FO20,148^BCN,90,N,N,N^FD{$barcode}^FS\n" .
                       "^XZ\n";
            $blocks[] = str_repeat($outerOne, max(1, $count));
            
            // 2) Beden etiketleri (bu renk için)
            foreach ($allSizes as $size) {
                $sizeSan = $this->sanitize((string)$size);
                $sizeOne = "^XA\n" .
                           "^CI28\n" .
                           "^PW500\n" .
                           "^LL300\n" .
                           "^LH10,10\n" .
                           "^FO10,10^GB480,280,2^FS\n" .
                           "^FO20,20^A0N,28,28^FD{$name}^FS\n" .
                           "^FO20,56^A0N,24,24^FD{$colorSan}^FS\n" .
                           "^FO20,88^A0N,26,26^FD{$sizeSan}^FS\n" .
                           "^FO20,122^A0N,20,20^FD{$barcode}^FS\n" .
                           "^FO370,16^BQN,2,2^FDLA,{$qrSeries}^FS\n" .
                           "^BY4,2,90\n" .
                           "^FO20,150^BCN,90,N,N,N^FD{$barcode}^FS\n" .
                           "^XZ\n";
                $blocks[] = str_repeat($sizeOne, max(1, $count));
            }
            
            // 3) Renk etiketi (bu renk için)
            $colorOne = "^XA\n" .
                       "^CI28\n" .
                       "^PW500\n" .
                       "^LL300\n" .
                       "^LH10,10\n" .
                       "^FO10,10^GB480,280,2^FS\n" .
                       "^FO20,20^A0N,28,28^FD{$name}^FS\n" .
                       "^FO20,56^A0N,24,24^FD{$colorSan}^FS\n" .
                       "^FO20,88^A0N,20,20^FD{$barcode}^FS\n" .
                       "^FO370,16^BQN,2,2^FDLA,{$qrSeries}^FS\n" .
                       "^BY4,2,90\n" .
                       "^FO20,116^BCN,90,N,N,N^FD{$barcode}^FS\n" .
                       "^XZ\n";
            $blocks[] = str_repeat($colorOne, max(1, $count));
            
            return implode('', $blocks);
        } else {
            // Dış etiket (sadece bu renk için)
            $one = "^XA\n" .    
                   "^CI28\n" .
                   "^PW500\n" .
                   "^LL300\n" .
                   "^LH10,10\n" .
                   "^FO10,10^GB480,280,2^FS\n" .
                   "^FO20,20^A0N,30,30^FD{$name}^FS\n" .
                   "^FO20,58^A0N,24,24^FD{$colorSan}^FS\n" .
                   ($sizesCsv !== '' ? "^FO20,90^A0N,22,22^FD{$sizesCsv}^FS\n" : '') .
                   "^FO20,120^A0N,20,20^FD{$barcode}^FS\n" .
                   "^FO360,15^BQN,2,3^FDLA,{$qrSeries}^FS\n" .
                   "^BY4,2,90\n" .
                   "^FO20,148^BCN,90,N,N,N^FD{$barcode}^FS\n" .
                   "^XZ\n";
            return str_repeat($one, max(1, $count));
        }
    }

   
    public function exportPdf(Request $request)
    {
        $items = $request->input('items', []);
        
        if (empty($items)) {
            return response()->json(['error' => 'No items provided'], 400);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('barcode.pdf-labels', [
            'items' => $items,
            'products' => $this->getProducts(),
            'series' => $this->getSeries()
        ]);

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'dpi' => 300,
            'defaultFont' => 'Arial',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);

        return $pdf->download('barkod_etiketleri_' . date('Y-m-d') . '.pdf');
    }

    private function getProducts()
    {
        return \App\Models\Product::with('colorVariants')->get();
    }

    private function getSeries()
    {
        return \App\Models\ProductSeries::with(['seriesItems', 'colorVariants'])->get();
    }

    /**
     * Generate QR code SVG for PDF fallback
     */
    public function generateQr(Request $request)
    {
        $text = $request->get('text', '');
        $size = max(20, (int) $request->get('size', 50));

        if (empty($text)) {
            return response()->json(['error' => 'No text provided'], 400);
        }

        try {
            $qrCode = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle($size, 1),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            
            $writer = new \BaconQrCode\Writer($qrCode);
            $qrImage = $writer->writeString($text);
            
            return response($qrImage)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Cache-Control', 'public, max-age=3600')
                ->header('Content-Disposition', 'inline');
                
        } catch (\Exception $e) {
            \Log::error('QR code generation failed', ['error' => $e->getMessage(), 'text' => $text]);
            return response()->json(['error' => 'QR code generation failed'], 500);
        }
    }

    /**
     * Download all color images as ZIP file
     * POST: items array with type, id, mode, count
     * 
     * Generates:
     * - outer mode: 1 PNG per color (outer label only)
     * - sizes mode: 1 PNG per color per size combination
     * - full mode: outer label per color + all size labels per color
     */
    public function downloadColorsZip(Request $request)
    {
        $items = $request->input('items', []);
        
        if (empty($items)) {
            return response()->json(['error' => 'No items provided'], 400);
        }

        // Create temporary directory for PNG files
        $tempDir = storage_path('app/temp/labels_' . uniqid());
        if (!\File::exists($tempDir)) {
            \File::makeDirectory($tempDir, 0755, true);
        }

        $pngFiles = [];
        $delay = 333; // 333ms = saniyede 3 istek (Labelary limit)
        $requestCount = 0;

        try {
            foreach ($items as $item) {
                if ($item['type'] !== 'series') {
                    continue;
                }

                $series = ProductSeries::with(['colorVariants', 'seriesItems'])->find($item['id']);
                if (!$series) {
                    continue;
                }

                $mode = $item['mode'] ?? 'outer';
                $colors = $series->colorVariants->pluck('color')->filter()->values()->all();
                $sizes = $series->seriesItems->pluck('size')->filter()->all();
                
                \Log::info('downloadColorsZip - Processing', [
                    'series_id' => $series->id,
                    'mode' => $mode,
                    'colors_count' => count($colors),
                    'sizes_count' => count($sizes),
                    'colors' => $colors,
                    'sizes' => $sizes
                ]);
                
                // Process based on mode
                if ($mode === 'full') {
                    // FULL mode: Outer label + all size labels for each color
                    if (empty($colors)) {
                        // No colors - generate single version
                        $zpl = $this->buildSeriesZplFull($series->id, 1);
                        if ($zpl !== null) {
                            $pngFiles[] = $this->saveLabelPng($zpl, $tempDir, "full", $requestCount, $delay);
                        }
                    } else {
                        // Each color gets: outer + all sizes
                        foreach ($colors as $color) {
                            // 1. Outer label for this color
                            $zpl = $this->buildSeriesZplByColor($series->id, $color, 'outer', 1);
                            if ($zpl !== null) {
                                $result = $this->saveLabelPng($zpl, $tempDir, "outer_" . str_replace(' ', '_', $color), $requestCount, $delay);
                                if ($result) $pngFiles[] = $result;
                            }
                            
                            // 2. Size labels for this color
                            foreach ($sizes as $size) {
                                $zpl = $this->buildSingleSizeZpl($series->id, $color, $size);
                                if ($zpl !== null) {
                                    $result = $this->saveLabelPng($zpl, $tempDir, str_replace(' ', '_', $color) . "_" . str_replace(' ', '_', $size), $requestCount, $delay);
                                    if ($result) $pngFiles[] = $result;
                                }
                            }
                        }
                    }
                } elseif ($mode === 'sizes') {
                    // SIZES mode: All size labels for each color (no outer label)
                    if (empty($colors)) {
                        // No colors - generate size labels without color
                        foreach ($sizes as $size) {
                            $zpl = $this->buildSeriesZpl($series->id, 'sizes', 1);
                            if ($zpl !== null) {
                                $result = $this->saveLabelPng($zpl, $tempDir, "size_" . str_replace(' ', '_', $size), $requestCount, $delay);
                                if ($result) $pngFiles[] = $result;
                            }
                        }
                    } else {
                        // Each color x each size combination
                        foreach ($colors as $color) {
                            foreach ($sizes as $size) {
                                $zpl = $this->buildSingleSizeZpl($series->id, $color, $size);
                                if ($zpl !== null) {
                                    $result = $this->saveLabelPng($zpl, $tempDir, str_replace(' ', '_', $color) . "_" . str_replace(' ', '_', $size), $requestCount, $delay);
                                    if ($result) $pngFiles[] = $result;
                                }
                            }
                        }
                    }
                } else {
                    // OUTER mode: Outer label for each color only
                    if (empty($colors)) {
                        // No colors - generate single outer label
                        $zpl = $this->buildSeriesZpl($series->id, 'outer', 1);
                        if ($zpl !== null) {
                            $result = $this->saveLabelPng($zpl, $tempDir, "outer", $requestCount, $delay);
                            if ($result) $pngFiles[] = $result;
                        }
                    } else {
                        // Each color gets one outer label
                        foreach ($colors as $color) {
                            $zpl = $this->buildSeriesZplByColor($series->id, $color, 'outer', 1);
                            if ($zpl !== null) {
                                $result = $this->saveLabelPng($zpl, $tempDir, "outer_" . str_replace(' ', '_', $color), $requestCount, $delay);
                                if ($result) $pngFiles[] = $result;
                            }
                        }
                    }
                }
            }

            \Log::info('downloadColorsZip - Final PNG files', [
                'png_files_count' => count($pngFiles),
                'png_files' => array_map('basename', $pngFiles)
            ]);

            if (empty($pngFiles)) {
                \File::deleteDirectory($tempDir);
                return response()->json(['error' => 'No PNG files generated'], 404);
            }

            // ZIP dosyası oluştur
            $zipFileName = 'etiketler_' . date('Y-m-d') . '_' . uniqid() . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);

            // Check if ZipArchive is available
            if (class_exists('ZipArchive')) {
                $zip = new \ZipArchive();
                if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                    \File::deleteDirectory($tempDir);
                    return response()->json(['error' => 'Failed to create ZIP file'], 500);
                }

                foreach ($pngFiles as $pngFile) {
                    if ($pngFile && \File::exists($pngFile)) {
                        $zip->addFile($pngFile, basename($pngFile));
                    }
                }

                $zip->close();
            } else {
                // Alternative: Use command line zip if available (Windows/Linux/Mac)
                // Try PowerShell on Windows, zip command on Linux/Mac
                $zipCommand = '';
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // Windows: Try PowerShell Compress-Archive
                    $zipCommand = "powershell -Command \"Compress-Archive -Path '" . str_replace('\\', '/', $tempDir) . "/*' -DestinationPath '" . str_replace('\\', '/', $zipPath) . "' -Force\"";
                } else {
                    // Linux/Mac: Use zip command
                    $zipCommand = "cd " . escapeshellarg($tempDir) . " && zip -r " . escapeshellarg($zipPath) . " .";
                }
                
                if (!empty($zipCommand)) {
                    exec($zipCommand, $output, $returnVar);
                }
                
                if (empty($zipCommand) || $returnVar !== 0) {
                    // Fallback: Create ZIP manually using simple method
                    // This is a basic ZIP implementation
                    $zipContent = $this->createSimpleZip($pngFiles);
                    \File::put($zipPath, $zipContent);
                }
            }

            // Geçici PNG dosyalarını sil
            \File::deleteDirectory($tempDir);

            // ZIP dosyasını indir
            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            // Hata durumunda temizlik
            if (\File::exists($tempDir)) {
                \File::deleteDirectory($tempDir);
            }
            \Log::error('Download colors ZIP error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to create ZIP: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper method to save a ZPL as PNG via Labelary API
     * Handles rate limiting automatically
     */
    private function saveLabelPng(string $zpl, string $tempDir, string $label, int &$requestCount, int $delay): ?string
    {
        try {
            // Rate limiting: wait before each request
            if ($requestCount > 0) {
                usleep($delay * 1000); // Convert ms to microseconds
            }
            $requestCount++;

            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders(['Accept' => 'image/png'])
                ->withOptions(['verify' => false])
                ->withBody($zpl, 'application/x-www-form-urlencoded')
                ->post('https://api.labelary.com/v1/printers/12dpmm/labels/1.97x1.18/0/');

            if ($response->successful() && $response->body()) {
                // Sanitize filename
                $safeLabel = preg_replace('/[^a-zA-Z0-9_-]/', '_', $label);
                $fileName = 'etiket_' . $safeLabel . '.png';
                $filePath = $tempDir . '/' . $fileName;
                \File::put($filePath, $response->body());
                
                \Log::info('saveLabelPng - Success', [
                    'label' => $label,
                    'fileName' => $fileName,
                    'fileSize' => strlen($response->body())
                ]);
                
                return $filePath;
            } else {
                \Log::error('saveLabelPng - Labelary API error', [
                    'label' => $label,
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500)
                ]);
                return null;
            }
        } catch (\Exception $e) {
            \Log::error('saveLabelPng - Exception', [
                'label' => $label,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Simple ZIP creation method (fallback when ZipArchive is not available)
     */
    private function createSimpleZip(array $files): string
    {
        // This is a very basic ZIP implementation
        // For production, consider using a proper ZIP library
        $zipData = '';
        $offset = 0;
        $centralDir = '';
        
        foreach ($files as $filePath) {
            if (!\File::exists($filePath)) {
                continue;
            }
            
            $fileName = basename($filePath);
            $fileContent = \File::get($filePath);
            $fileSize = strlen($fileContent);
            $crc32 = crc32($fileContent);
            
            // Local file header
            $localHeader = pack('V', 0x04034b50); // Local file header signature
            $localHeader .= pack('v', 20); // Version needed to extract
            $localHeader .= pack('v', 0); // General purpose bit flag
            $localHeader .= pack('v', 0); // Compression method (0 = stored)
            $localHeader .= pack('v', 0); // Last mod file time
            $localHeader .= pack('v', 0); // Last mod file date
            $localHeader .= pack('V', $crc32); // CRC-32
            $localHeader .= pack('V', $fileSize); // Compressed size
            $localHeader .= pack('V', $fileSize); // Uncompressed size
            $localHeader .= pack('v', strlen($fileName)); // File name length
            $localHeader .= pack('v', 0); // Extra field length
            $localHeader .= $fileName; // File name
            
            $zipData .= $localHeader . $fileContent;
            
            // Central directory file header
            $centralDir .= pack('V', 0x02014b50); // Central file header signature
            $centralDir .= pack('v', 20); // Version made by
            $centralDir .= pack('v', 20); // Version needed to extract
            $centralDir .= pack('v', 0); // General purpose bit flag
            $centralDir .= pack('v', 0); // Compression method
            $centralDir .= pack('v', 0); // Last mod file time
            $centralDir .= pack('v', 0); // Last mod file date
            $centralDir .= pack('V', $crc32); // CRC-32
            $centralDir .= pack('V', $fileSize); // Compressed size
            $centralDir .= pack('V', $fileSize); // Uncompressed size
            $centralDir .= pack('v', strlen($fileName)); // File name length
            $centralDir .= pack('v', 0); // Extra field length
            $centralDir .= pack('v', 0); // File comment length
            $centralDir .= pack('v', 0); // Disk number start
            $centralDir .= pack('v', 0); // Internal file attributes
            $centralDir .= pack('V', 0); // External file attributes
            $centralDir .= pack('V', $offset); // Relative offset of local header
            $centralDir .= $fileName; // File name
            
            $offset += strlen($localHeader) + $fileSize;
        }
        
        // End of central directory record
        $endOfCentralDir = pack('V', 0x06054b50); // End of central dir signature
        $endOfCentralDir .= pack('v', 0); // Number of this disk
        $endOfCentralDir .= pack('v', 0); // Number of the disk with the start of the central directory
        $endOfCentralDir .= pack('v', count($files)); // Total number of entries in the central directory on this disk
        $endOfCentralDir .= pack('v', count($files)); // Total number of entries in the central directory
        $endOfCentralDir .= pack('V', strlen($centralDir)); // Size of the central directory
        $endOfCentralDir .= pack('V', strlen($zipData)); // Offset of start of central directory with respect to the starting disk number
        $endOfCentralDir .= pack('v', 0); // ZIP file comment length
        
        return $zipData . $centralDir . $endOfCentralDir;
    }
}



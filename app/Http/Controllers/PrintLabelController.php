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

        $url = 'https://api.labelary.com/v1/printers/12dpmm/labels/1.97x1.18/0/';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $zpl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: image/png',
            'Content-Type: application/x-www-form-urlencoded',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        // Dev ortamında SSL problemleri için (prod'da açmayın):
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $png = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $respHeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($code >= 200 && $code < 300 && $png) {
            return response($png, 200, [ 'Content-Type' => 'image/png' ]);
        }

        return response()->json([
            'message' => 'Labelary render failed',
            'status' => $code,
            'error' => $err,
            'body' => is_string($png) ? substr($png, 0, 500) : null,
        ], 502);
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
        // ZPL basit ASCII ile daha stabil çalışır; problemli karakterleri sadeleştir.
        $replacements = [
            'Ş'=>'S','ş'=>'s','Ğ'=>'G','ğ'=>'g','İ'=>'I','ı'=>'i','Ö'=>'O','ö'=>'o','Ü'=>'U','ü'=>'u','Ç'=>'C','ç'=>'c',
        ];
        $value = strtr($value, $replacements);
        // ZPL için ^, ~ gibi kontrol karakterlerini kaçır.
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
        $name = $this->sanitize($series->name ?? '');
        $seriesSize = (int) ($series->series_size ?? 0);
        $barcode = $this->sanitize($series->barcode ?: ($series->sku ?: ('S' . str_pad((string)$series->id, 4, '0', STR_PAD_LEFT))));
        
        // TÜM bedenler - tekrar edenlerle birlikte (unique değil!)
        $allSizes = $series->seriesItems->pluck('size')->filter()->all();
        
        $colors = $series->colorVariants->pluck('color')->filter()->values()->all();
        $qrSeries = url('/products/series/' . $series->id);

        // Dış pakette TÜM bedenler gösterilecek (tekrarlılar dahil!)
        $sizesCsv = $this->sanitize(implode(' ', $allSizes));
        $colorsCsv = $this->sanitize(implode(', ', $colors));

        if ($mode === 'sizes') {
            // Her beden için ayrı etiket üret - AYNI BEDENDEN VARSA HEPSİ
            $blocks = [];
            
            // TÜM bedenler (tekrarlı olanlar dahil)
            foreach ($allSizes as $size) {
                $sizeSan = $this->sanitize((string)$size);

                // QR'ı daha küçük yap ve sağ üstte sabit tut; barkodu alta indir
                $one = "^XA\n" .
                       "^PW500\n" .
                       "^LL300\n" .
                       "^LH10,10\n" .
                       "^FO10,10^GB480,280,2^FS\n" .
                       // Üst sol metin alanı
                       "^FO20,20^A0N,24,24^FD{$category}^FS\n" .
                       "^FO20,50^A0N,28,28^FD{$name}^FS\n" .
                       "^FO20,86^A0N,26,26^FDBEDEN: {$sizeSan}^FS\n" .
                       "^FO20,116^A0N,22,22^FDSeri: " . ($seriesSize > 0 ? $seriesSize . "'li" : 'Normal') . "^FS\n" .
                       // Barkod metni (barkoddan önce)
                       "^FO20,146^A0N,20,20^FD{$barcode}^FS\n" .
                       // QR sağ üst (daha küçük):
                       "^FO370,16^BQN,2,2^FDLA,{$qrSeries}^FS\n" .
                       // Barkod en altta, yüksekliği azaltılmış
                       "^BY3,2,85\n" .
                       "^FO20,175^BCN,85,N,N,N^FD{$barcode}^FS\n" .
                       "^XZ\n";
                $blocks[] = str_repeat($one, max(1, $count));
            }
            return implode('', $blocks);
        }

        // DIŞ paket etiketi (OUTER mode)
        $seriesInfo = $seriesSize > 0 ? ($seriesSize . "'li SERI") : 'SERI';
        $year = date('Y');

        $one = "^XA\n" .    
               "^PW500\n" .
               "^LL300\n" .
               "^LH10,10\n" .
               "^FO10,10^GB480,280,2^FS\n" .
               "^FO20,20^A0N,24,24^FD{$category}^FS\n" .
               "^FO20,48^A0N,30,30^FD{$name}^FS\n" .
               "^FO360,15^BQN,2,3^FDLA,{$qrSeries}^FS\n" .
               "^FO20,80^A0N,26,26^FD{$seriesInfo} {$year}^FS\n" .
               ($sizesCsv !== '' ? "^FO20,110^A0N,22,22^FDBedenler:^FS\n^FO20,135^A0N,20,20^FD{$sizesCsv}^FS\n" : '') .
               "^FO20,163^A0N,20,20^FD{$barcode}^FS\n" .
               "^BY4,2,90\n" .
               "^FO20,188^BCN,90,N,N,N^FD{$barcode}^FS\n" .
               "^XZ\n";
        return str_repeat($one, max(1, $count));
    }

    // Full sequence for series: per package prints [OUTER + each size] in order.
    private function buildSeriesZplFull(int $seriesId, int $packages = 1): ?string
    {
        $series = ProductSeries::with(['seriesItems', 'colorVariants'])->find($seriesId);
        if (!$series) return null;

        $outer = $this->buildSeriesZpl($seriesId, 'outer', 1);
        $sizesZpl = $this->buildSeriesZpl($seriesId, 'sizes', 1);
        if ($outer === null || $sizesZpl === null) return null;

        $sequence = $outer . $sizesZpl; // OUTER + all sizes (order preserved)
        return str_repeat($sequence, max(1, $packages));
    }

    /**
     * PDF Export for barcode labels (A4 landscape, 5x3 grid)
     */
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
}



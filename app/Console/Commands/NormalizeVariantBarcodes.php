<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductSeries;
use App\Models\ProductSeriesColorVariant;

class NormalizeVariantBarcodes extends Command
{
    protected $signature = 'ronex:normalize-variant-barcodes {--series_id=* : Only normalize given series IDs}';

    protected $description = 'Normalize all series color variant barcodes to short base+counter format (e.g., K0011, K0012). Also fills missing QR codes.';

    public function handle(): int
    {
        $seriesIds = collect($this->option('series_id'))->filter()->map(fn($v) => (int)$v)->all();

        $query = ProductSeries::with('colorVariants');
        if (!empty($seriesIds)) {
            $query->whereIn('id', $seriesIds);
        }

        $countSeries = 0;
        $countVariantsUpdated = 0;

        $query->chunkById(100, function ($chunk) use (&$countSeries, &$countVariantsUpdated) {
            /** @var ProductSeries $series */
            foreach ($chunk as $series) {
                $countSeries++;
                $base = (string)($series->barcode ?: ($series->sku ?: ('S' . $series->id)));
                $base = preg_replace('/\s+/', '', $base);
                if ($base === '') {
                    $base = 'S' . $series->id;
                }

                // Calculate next suffix based on already normalized variants (prefix == base and numeric suffix)
                $existingSuffixes = ProductSeriesColorVariant::where('product_series_id', $series->id)
                    ->whereNotNull('barcode')
                    ->pluck('barcode')
                    ->map(function ($code) use ($base) {
                        if (strpos($code, $base) === 0) {
                            $suffix = substr($code, strlen($base));
                            return ctype_digit($suffix) && $suffix !== '' ? (int)$suffix : null;
                        }
                        return null;
                    })
                    ->filter()
                    ->values()
                    ->all();

                $next = empty($existingSuffixes) ? 1 : (max($existingSuffixes) + 1);

                foreach ($series->colorVariants()->orderBy('id')->get() as $variant) {
                    $code = (string)($variant->barcode ?? '');
                    $needsRecode = ($code === '')
                        || preg_match('/^(SV|PV)/', $code) === 1
                        || strpos($code, $base) !== 0;

                    if ($needsRecode) {
                        // Find next free candidate
                        $candidate = $base . $next;
                        while (ProductSeriesColorVariant::where('barcode', $candidate)->where('id', '!=', $variant->id)->exists()) {
                            $next++;
                            $candidate = $base . $next;
                        }
                        $variant->barcode = $candidate;
                        if (empty($variant->qr_code_value)) {
                            $variant->qr_code_value = route('products.series.color', ['series' => $series->id, 'variant' => $variant->id]);
                        }
                        $variant->save();
                        $countVariantsUpdated++;
                        $next++;
                        $this->line("Updated series #{$series->id} variant #{$variant->id} -> {$variant->barcode}");
                    }
                }
            }
        });

        $this->info("Processed series: {$countSeries}, updated variants: {$countVariantsUpdated}");
        return Command::SUCCESS;
    }
}



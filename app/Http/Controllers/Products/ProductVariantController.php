<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSeries;
use App\Models\ProductColorVariant;
use App\Models\ProductSeriesColorVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
	private function currentAccountId(): ?int
	{
		$accountId = session('current_account_id');
		return $accountId ? (int) $accountId : null;
	}

	private function assertBelongsToCurrentAccount(?int $modelAccountId): void
	{
		$accountId = $this->currentAccountId();
		if ($accountId !== null && (int) ($modelAccountId ?? 0) !== $accountId) {
			abort(404);
		}
	}

	/**
	 * Show a product focused on a specific color variant.
	 */
	public function productColor(Product $product, ProductColorVariant $variant)
	{
		$this->assertBelongsToCurrentAccount($product->account_id);
		if ($variant->product_id !== $product->id) {
			abort(404);
		}

		return view('products.variant', [
			'type' => 'product',
			'product' => $product->loadMissing('colorVariants'),
			'series' => null,
			'variant' => $variant,
		]);
	}

	/**
	 * Show a product focused on a color name.
	 */
	public function productColorByName(Product $product, string $color)
	{
		$this->assertBelongsToCurrentAccount($product->account_id);
		$normalized = mb_strtolower($color);
		$variant = ProductColorVariant::where('product_id', $product->id)
			->whereRaw('LOWER(color) = ?', [$normalized])
			->first();
		if (!$variant) {
			// fallback partial
			$variant = ProductColorVariant::where('product_id', $product->id)
				->where('color', 'like', '%' . $color . '%')
				->first();
		}
		abort_unless($variant, 404);

		return view('products.variant', [
			'type' => 'product',
			'product' => $product->loadMissing('colorVariants'),
			'series' => null,
			'variant' => $variant,
		]);
	}

	/**
	 * Show a series focused on a specific color variant.
	 */
	public function seriesColor(ProductSeries $series, ProductSeriesColorVariant $variant)
	{
		$this->assertBelongsToCurrentAccount($series->account_id);
		if ($variant->product_series_id !== $series->id) {
			abort(404);
		}

		return view('products.variant', [
			'type' => 'series',
			'product' => null,
			'series' => $series->loadMissing(['seriesItems', 'colorVariants']),
			'variant' => $variant,
		]);
	}

	/**
	 * Show a series focused on a color name.
	 */
	public function seriesColorByName(ProductSeries $series, string $color)
	{
		$this->assertBelongsToCurrentAccount($series->account_id);
		$normalized = mb_strtolower($color);
		$variant = ProductSeriesColorVariant::where('product_series_id', $series->id)
			->whereRaw('LOWER(color) = ?', [$normalized])
			->first();
		if (!$variant) {
			// fallback partial
			$variant = ProductSeriesColorVariant::where('product_series_id', $series->id)
				->where('color', 'like', '%' . $color . '%')
				->first();
		}
		abort_unless($variant, 404);

		return view('products.variant', [
			'type' => 'series',
			'product' => null,
			'series' => $series->loadMissing(['seriesItems', 'colorVariants']),
			'variant' => $variant,
		]);
	}
}



<?php

namespace Tests\Feature\Products;

use App\Models\Account;
use App\Models\ProductCategory;
use App\Models\ProductSeries;
use App\Models\ProductSeriesColorVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSeriesTest extends TestCase
{
    use RefreshDatabase;

    private function seedAccountAndLogin(): array
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $account = Account::create([
            'name' => 'Test Account',
            'code' => 'TST',
            'is_active' => true,
        ]);

        // AccountSelectionMiddleware relies on this session key
        $this->withSession(['current_account_id' => $account->id]);

        $category = ProductCategory::create([
            'account_id' => $account->id,
            'name' => 'Shirts',
            'is_active' => true,
        ]);

        return [$user, $account, $category];
    }

    public function test_can_create_series_with_items_and_color_variants_and_stock_is_consistent(): void
    {
        [, $account] = $this->seedAccountAndLogin();

        $payload = [
            'name' => 'Test Series',
            'category' => 'Shirts',
            'sku' => 'TS-001',
            'barcode' => 'TSB-001',
            'sizes' => ['S', 'M'],
            'quantities' => [1, 2],
            'color_variants' => [
                ['color' => 'Red', 'stock_quantity' => 5, 'critical_stock' => 1],
                ['color' => 'Blue', 'stock_quantity' => 0, 'critical_stock' => 0],
            ],
            'is_active' => 1,
        ];

        $response = $this->post(route('products.series.store'), $payload);
        $response->assertRedirect(route('products.series.index'));

        $series = ProductSeries::query()
            ->where('account_id', $account->id)
            ->where('name', 'Test Series')
            ->firstOrFail();

        $this->assertSame(3, (int) $series->series_size, 'series_size must equal sum(quantities).');
        $this->assertSame(5, (int) $series->stock_quantity, 'stock_quantity must equal sum(variant stocks).');

        $this->assertDatabaseCount('product_series_items', 2);
        $this->assertDatabaseCount('product_series_color_variants', 2);

        $variants = ProductSeriesColorVariant::where('product_series_id', $series->id)->get();
        $this->assertTrue($variants->every(fn ($v) => !empty($v->barcode)), 'Each color variant should have a generated barcode.');
    }

    public function test_quick_stock_update_increments_all_variants_and_updates_series_total(): void
    {
        [, $account] = $this->seedAccountAndLogin();

        $series = ProductSeries::create([
            'account_id' => $account->id,
            'name' => 'Series A',
            'category' => 'Shirts',
            'cost' => 0,
            'price' => 0,
            'series_size' => 1,
            'stock_quantity' => 0,
            'critical_stock' => 0,
            'is_active' => true,
        ]);

        $v1 = $series->colorVariants()->create([
            'color' => 'Black',
            'stock_quantity' => 5,
            'critical_stock' => 0,
            'is_active' => true,
        ]);
        $v2 = $series->colorVariants()->create([
            'color' => 'White',
            'stock_quantity' => 0,
            'critical_stock' => 0,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('products.series.quick-stock', $series), [
            'add_stock' => 3,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $v1->refresh();
        $v2->refresh();
        $series->refresh();

        $this->assertSame(8, (int) $v1->stock_quantity);
        $this->assertSame(3, (int) $v2->stock_quantity);
        $this->assertSame(11, (int) $series->stock_quantity);
    }

    public function test_series_detail_is_scoped_to_current_account(): void
    {
        [, $account1] = $this->seedAccountAndLogin();

        $account2 = Account::create([
            'name' => 'Other Account',
            'code' => 'OTH',
            'is_active' => true,
        ]);
        ProductCategory::create([
            'account_id' => $account2->id,
            'name' => 'Shirts',
            'is_active' => true,
        ]);

        $foreignSeries = ProductSeries::create([
            'account_id' => $account2->id,
            'name' => 'Foreign Series',
            'category' => 'Shirts',
            'cost' => 0,
            'price' => 0,
            'series_size' => 1,
            'stock_quantity' => 0,
            'critical_stock' => 0,
            'is_active' => true,
        ]);

        // Ensure current account is account1
        $this->withSession(['current_account_id' => $account1->id]);

        $this->get(route('products.series.show', $foreignSeries))->assertNotFound();
    }

    public function test_update_can_add_new_color_variant_without_deleting_existing(): void
    {
        [, $account] = $this->seedAccountAndLogin();

        $series = ProductSeries::create([
            'account_id' => $account->id,
            'name' => 'Series B',
            'category' => 'Shirts',
            'cost' => 0,
            'price' => 0,
            'series_size' => 1,
            'stock_quantity' => 0,
            'critical_stock' => 0,
            'is_active' => true,
        ]);

        $existing = $series->colorVariants()->create([
            'color' => 'Green',
            'stock_quantity' => 2,
            'critical_stock' => 0,
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Series B',
            'category' => 'Shirts',
            'sku' => null,
            'barcode' => null,
            'cost' => 0,
            'price' => 0,
            'is_active' => 1,
            'color_variants' => [
                // existing variant update
                (string) $existing->id => [
                    'stock_quantity' => 2,
                    'critical_stock' => 0,
                    'is_active' => true,
                ],
                // new variant creation (matches edit.blade.php "new_x" convention)
                'new_0' => [
                    'color' => 'Orange',
                    'stock_quantity' => 1,
                    'critical_stock' => 0,
                    'is_active' => true,
                ],
            ],
        ];

        $this->put(route('products.series.update', $series), $payload)
            ->assertRedirect(route('products.series.index'));

        $this->assertDatabaseHas('product_series_color_variants', [
            'product_series_id' => $series->id,
            'color' => 'Green',
        ]);
        $this->assertDatabaseHas('product_series_color_variants', [
            'product_series_id' => $series->id,
            'color' => 'Orange',
        ]);
    }

    public function test_api_series_colors_is_scoped_to_current_account(): void
    {
        [, $account1] = $this->seedAccountAndLogin();

        $account2 = Account::create([
            'name' => 'Other Account',
            'code' => 'OTH2',
            'is_active' => true,
        ]);

        $foreignSeries = ProductSeries::create([
            'account_id' => $account2->id,
            'name' => 'Foreign Series 2',
            'category' => 'Shirts',
            'cost' => 0,
            'price' => 0,
            'series_size' => 1,
            'stock_quantity' => 0,
            'critical_stock' => 0,
            'is_active' => true,
        ]);
        $foreignSeries->colorVariants()->create([
            'color' => 'Purple',
            'stock_quantity' => 0,
            'critical_stock' => 0,
            'is_active' => true,
        ]);

        // With account1 selected, foreign series should not be resolvable
        $this->withSession(['current_account_id' => $account1->id]);
        $this->getJson("/api/series/{$foreignSeries->id}/colors")
            ->assertStatus(404);
    }
}


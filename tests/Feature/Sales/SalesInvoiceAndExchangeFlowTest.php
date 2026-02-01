<?php

namespace Tests\Feature\Sales;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ProductCategory;
use App\Models\ProductSeries;
use App\Models\ProductSeriesColorVariant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesInvoiceAndExchangeFlowTest extends TestCase
{
    use RefreshDatabase;

    protected int $startingObLevel = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->startingObLevel = ob_get_level();
    }

    protected function tearDown(): void
    {
        // Some rendered pages may start output buffering (often via vendor code).
        // Close any buffers opened during a test to prevent PHPUnit "risky test" failures.
        while (ob_get_level() > $this->startingObLevel) {
            @ob_end_clean();
        }
        parent::tearDown();
    }

    private function signInWithAccount(): array
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $account = Account::create([
            'name' => 'Test Account',
            'code' => 'TST',
            'is_active' => true,
        ]);
        $this->withSession(['current_account_id' => $account->id]);

        // ProductSeriesController expects allowed categories to exist per account (by name)
        ProductCategory::create([
            'account_id' => $account->id,
            'name' => 'Shirts',
            'is_active' => true,
        ]);

        return [$user, $account];
    }

    private function makeCustomer(): Customer
    {
        return Customer::create([
            'name' => 'Acme Customer',
            'phone' => '555-0000',
            'is_active' => true,
        ]);
    }

    private function makeSeriesWithVariant(int $accountId, int $stock = 10): array
    {
        $series = ProductSeries::create([
            'account_id' => $accountId,
            'name' => 'Series Shirt',
            'category' => 'Shirts',
            'cost' => 0,
            'price' => 0,
            'series_size' => 1,
            'stock_quantity' => 0,
            'critical_stock' => 0,
            'is_active' => true,
        ]);

        $variant = $series->colorVariants()->create([
            'color' => 'Red',
            'stock_quantity' => $stock,
            'critical_stock' => 0,
            'is_active' => true,
        ]);

        // Keep series aggregate stock coherent
        $series->stock_quantity = (int) $series->colorVariants()->sum('stock_quantity');
        $series->save();

        return [$series, $variant];
    }

    public function test_sales_invoices_create_page_loads_and_contains_customer_modal(): void
    {
        $this->signInWithAccount();

        $this->get(route('sales.invoices.create'))
            ->assertOk()
            ->assertSee('id="invoiceForm"', false)
            ->assertSee('id="customerModal"', false);
    }

    public function test_can_store_invoice_with_series_variant_and_stock_is_deducted(): void
    {
        [, $account] = $this->signInWithAccount();
        $customer = $this->makeCustomer();
        [$series, $variant] = $this->makeSeriesWithVariant($account->id, 10);

        $payload = [
            'customer_id' => $customer->id,
            'invoice_date' => Carbon::now()->format('Y-m-d'),
            'invoice_time' => Carbon::now()->format('H:i'),
            'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'currency' => 'TRY',
            'vat_status' => 'included',
            'description' => 'Test invoice',
            'payment_completed' => 0,
            'items' => [
                [
                    'type' => 'series',
                    'product_id' => 'series_' . $series->id,
                    'product_service_name' => $series->name,
                    'description' => '',
                    'quantity' => 2,
                    'unit_price' => 100,
                    'unit_currency' => 'TRY',
                    'tax_rate' => 10,
                    'discount_rate' => 0,
                    'color_variant_id' => $variant->id,
                    'selected_color' => $variant->color,
                ],
            ],
        ];

        $response = $this->post(route('sales.invoices.store'), $payload);
        $response->assertStatus(302);

        $invoice = Invoice::query()->latest('id')->firstOrFail();
        $this->assertSame($account->id, (int) $invoice->account_id);
        $this->assertSame($customer->id, (int) $invoice->customer_id);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'product_type' => 'series',
            'product_id' => $series->id,
            'color_variant_id' => $variant->id,
            'is_return' => 0,
        ]);

        $variant->refresh();
        $this->assertSame(8, (int) $variant->stock_quantity, 'Variant stock should be decremented by sold quantity.');
    }

    public function test_store_invoice_rejects_series_line_without_color_when_series_has_variants(): void
    {
        [, $account] = $this->signInWithAccount();
        $customer = $this->makeCustomer();
        [$series] = $this->makeSeriesWithVariant($account->id, 10);

        $payload = [
            'customer_id' => $customer->id,
            'invoice_date' => Carbon::now()->format('Y-m-d'),
            'invoice_time' => Carbon::now()->format('H:i'),
            'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'currency' => 'TRY',
            'vat_status' => 'included',
            'items' => [
                [
                    'type' => 'series',
                    'product_id' => 'series_' . $series->id,
                    'product_service_name' => $series->name,
                    'quantity' => 1,
                    'unit_price' => 100,
                    'unit_currency' => 'TRY',
                    'tax_rate' => 10,
                    'discount_rate' => 0,
                    // missing color_variant_id on purpose
                ],
            ],
        ];

        $this->from(route('sales.invoices.create'))
            ->post(route('sales.invoices.store'), $payload)
            ->assertStatus(302)
            ->assertRedirect(route('sales.invoices.create'))
            ->assertSessionHas('error');
    }

    public function test_sales_invoices_edit_page_loads_for_existing_invoice(): void
    {
        [, $account] = $this->signInWithAccount();
        $customer = $this->makeCustomer();
        [$series, $variant] = $this->makeSeriesWithVariant($account->id, 10);

        // Create via controller to ensure stored shape matches update expectations
        $this->post(route('sales.invoices.store'), [
            'customer_id' => $customer->id,
            'invoice_date' => Carbon::now()->format('Y-m-d'),
            'invoice_time' => Carbon::now()->format('H:i'),
            'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'currency' => 'TRY',
            'vat_status' => 'included',
            'payment_completed' => 0,
            'items' => [[
                'type' => 'series',
                'product_id' => 'series_' . $series->id,
                'product_service_name' => $series->name,
                'quantity' => 1,
                'unit_price' => 100,
                'unit_currency' => 'TRY',
                'tax_rate' => 10,
                'discount_rate' => 0,
                'color_variant_id' => $variant->id,
                'selected_color' => $variant->color,
            ]],
        ]);

        $invoice = Invoice::query()->latest('id')->firstOrFail();

        $this->get(route('sales.invoices.edit', $invoice))
            ->assertOk()
            ->assertSee('id="invoiceForm"', false)
            ->assertSee('id="customerModal"', false);
    }

    public function test_sales_invoices_show_contains_refund_button_and_modal_and_exchange_link(): void
    {
        [, $account] = $this->signInWithAccount();
        $customer = $this->makeCustomer();
        [$series, $variant] = $this->makeSeriesWithVariant($account->id, 10);

        $this->post(route('sales.invoices.store'), [
            'customer_id' => $customer->id,
            'invoice_date' => Carbon::now()->format('Y-m-d'),
            'invoice_time' => Carbon::now()->format('H:i'),
            'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'currency' => 'TRY',
            'vat_status' => 'included',
            'payment_completed' => 0,
            'items' => [[
                'type' => 'series',
                'product_id' => 'series_' . $series->id,
                'product_service_name' => $series->name,
                'quantity' => 1,
                'unit_price' => 100,
                'unit_currency' => 'TRY',
                'tax_rate' => 10,
                'discount_rate' => 0,
                'color_variant_id' => $variant->id,
                'selected_color' => $variant->color,
            ]],
        ]);

        $invoice = Invoice::query()->latest('id')->firstOrFail();

        $this->get(route('sales.invoices.show', $invoice))
            ->assertOk()
            ->assertSee('data-bs-target="#addReturnModal"', false)
            ->assertSee('id="addReturnModal"', false)
            ->assertSee(route('sales.exchanges.create', $invoice), false);
    }

    public function test_can_add_refund_return_item_and_stock_is_restored(): void
    {
        [, $account] = $this->signInWithAccount();
        $customer = $this->makeCustomer();
        [$series, $variant] = $this->makeSeriesWithVariant($account->id, 10);

        $this->post(route('sales.invoices.store'), [
            'customer_id' => $customer->id,
            'invoice_date' => Carbon::now()->format('Y-m-d'),
            'invoice_time' => Carbon::now()->format('H:i'),
            'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'currency' => 'TRY',
            'vat_status' => 'included',
            'payment_completed' => 0,
            'items' => [[
                'type' => 'series',
                'product_id' => 'series_' . $series->id,
                'product_service_name' => $series->name,
                'quantity' => 2,
                'unit_price' => 100,
                'unit_currency' => 'TRY',
                'tax_rate' => 10,
                'discount_rate' => 0,
                'color_variant_id' => $variant->id,
                'selected_color' => $variant->color,
            ]],
        ]);

        $invoice = Invoice::query()->latest('id')->firstOrFail();
        $originalItem = $invoice->items()->where('is_return', false)->firstOrFail();

        $variant->refresh();
        $this->assertSame(8, (int) $variant->stock_quantity);

        $this->post(route('sales.invoices.add-return', $invoice), [
            'invoice_item_id' => $originalItem->id,
            'quantity' => 1,
        ])->assertStatus(302);

        $originalItem->refresh();
        $this->assertSame(1.0, (float) $originalItem->quantity, 'Original item quantity should be reduced by returned qty.');

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'is_return' => 1,
            'product_id' => $series->id,
            'color_variant_id' => $variant->id,
        ]);

        $variant->refresh();
        $this->assertSame(9, (int) $variant->stock_quantity, 'Refund should add stock back.');
    }

    public function test_update_invoice_restores_old_stock_then_deducts_new_stock(): void
    {
        [, $account] = $this->signInWithAccount();
        $customer = $this->makeCustomer();
        [$series, $variant] = $this->makeSeriesWithVariant($account->id, 10);

        $this->post(route('sales.invoices.store'), [
            'customer_id' => $customer->id,
            'invoice_date' => Carbon::now()->format('Y-m-d'),
            'invoice_time' => Carbon::now()->format('H:i'),
            'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'currency' => 'TRY',
            'vat_status' => 'included',
            'payment_completed' => 0,
            'items' => [[
                'type' => 'series',
                'product_id' => 'series_' . $series->id,
                'product_service_name' => $series->name,
                'quantity' => 2,
                'unit_price' => 100,
                'unit_currency' => 'TRY',
                'tax_rate' => 10,
                'discount_rate' => 0,
                'color_variant_id' => $variant->id,
                'selected_color' => $variant->color,
            ]],
        ]);
        $invoice = Invoice::query()->latest('id')->firstOrFail();

        $variant->refresh();
        $this->assertSame(8, (int) $variant->stock_quantity);

        // Update invoice to quantity 1 (net stock should become 9)
        $this->put(route('sales.invoices.update', $invoice), [
            'customer_id' => $customer->id,
            'invoice_date' => Carbon::now()->format('Y-m-d'),
            'invoice_time' => Carbon::now()->format('H:i'),
            'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'currency' => 'TRY',
            'vat_status' => 'included',
            'payment_completed' => 0,
            'items' => [[
                'type' => 'series',
                'product_id' => 'series_' . $series->id,
                'product_service_name' => $series->name,
                'quantity' => 1,
                'unit_price' => 100,
                'unit_currency' => 'TRY',
                'tax_rate' => 10,
                'discount_rate' => 0,
                'color_variant_id' => $variant->id,
                'selected_color' => $variant->color,
            ]],
        ])->assertStatus(302);

        $variant->refresh();
        $this->assertSame(9, (int) $variant->stock_quantity);
    }

    public function test_sales_exchange_create_page_loads_and_exchange_form_is_present(): void
    {
        [, $account] = $this->signInWithAccount();
        $customer = $this->makeCustomer();
        [$series, $variant] = $this->makeSeriesWithVariant($account->id, 10);

        $this->post(route('sales.invoices.store'), [
            'customer_id' => $customer->id,
            'invoice_date' => Carbon::now()->format('Y-m-d'),
            'invoice_time' => Carbon::now()->format('H:i'),
            'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'currency' => 'TRY',
            'vat_status' => 'included',
            'payment_completed' => 0,
            'items' => [[
                'type' => 'series',
                'product_id' => 'series_' . $series->id,
                'product_service_name' => $series->name,
                'quantity' => 2,
                'unit_price' => 100,
                'unit_currency' => 'TRY',
                'tax_rate' => 10,
                'discount_rate' => 0,
                'color_variant_id' => $variant->id,
                'selected_color' => $variant->color,
            ]],
        ]);
        $invoice = Invoice::query()->latest('id')->firstOrFail();

        $this->get(route('sales.exchanges.create', $invoice))
            ->assertOk()
            ->assertSee('id="exchangeForm"', false)
            ->assertSee(route('sales.exchanges.store', $invoice), false);
    }

    public function test_can_store_exchange_with_new_item_and_stock_is_balanced(): void
    {
        [, $account] = $this->signInWithAccount();
        $customer = $this->makeCustomer();
        [$series, $variant] = $this->makeSeriesWithVariant($account->id, 10);

        // Invoice sells 2 units: stock goes 10 -> 8
        $this->post(route('sales.invoices.store'), [
            'customer_id' => $customer->id,
            'invoice_date' => Carbon::now()->format('Y-m-d'),
            'invoice_time' => Carbon::now()->format('H:i'),
            'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'currency' => 'TRY',
            'vat_status' => 'included',
            'payment_completed' => 0,
            'items' => [[
                'type' => 'series',
                'product_id' => 'series_' . $series->id,
                'product_service_name' => $series->name,
                'quantity' => 2,
                'unit_price' => 100,
                'unit_currency' => 'TRY',
                'tax_rate' => 10,
                'discount_rate' => 0,
                'color_variant_id' => $variant->id,
                'selected_color' => $variant->color,
            ]],
        ]);
        $invoice = Invoice::query()->latest('id')->firstOrFail();
        $originalItem = $invoice->items()->where('is_return', false)->firstOrFail();

        $variant->refresh();
        $this->assertSame(8, (int) $variant->stock_quantity);

        // Exchange: return 1 unit and add 1 new unit (same series/variant for simplicity)
        $exchangePayload = [
            'customer_id' => $customer->id,
            'currency' => 'TRY',
            'vat_status' => 'included',
            'invoice_date' => Carbon::now()->format('Y-m-d'),
            'invoice_time' => Carbon::now()->format('H:i'),
            'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'description' => 'Test exchange',
            'payment_completed' => 0,
            'exchange_items' => [
                [
                    'original_item_id' => $originalItem->id,
                    'exchange_quantity' => 1,
                    'new_item' => [
                        'type' => 'series',
                        'product_id' => 'series_' . $series->id,
                        'product_service_name' => $series->name,
                        'quantity' => 1,
                        'unit_price' => 100,
                        'discount_rate' => 0,
                        'tax_rate' => 10,
                        'color_variant_id' => $variant->id,
                        'selected_color' => $variant->color,
                    ],
                ],
            ],
        ];

        $this->post(route('sales.exchanges.store', $invoice), $exchangePayload)
            ->assertStatus(302)
            ->assertRedirect(route('sales.invoices.show', $invoice));

        // Verify an exchange line item was created (description prefix)
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            // ExchangeController only appends the new_item.description (not the top-level exchange description)
            'description' => 'Değişim - ' . $series->name,
        ]);

        // Original item quantity should be reduced by exchange_quantity
        $originalItem->refresh();
        $this->assertSame(1.0, (float) $originalItem->quantity);

        // Stock: +1 from returned, -1 for new item => unchanged (still 8)
        $variant->refresh();
        $this->assertSame(8, (int) $variant->stock_quantity);

        // Exchange rows created
        $this->assertDatabaseHas('exchanges', [
            'original_invoice_id' => $invoice->id,
            'new_invoice_id' => $invoice->id,
        ]);
        $this->assertDatabaseHas('exchange_items', [
            'original_item_id' => $originalItem->id,
        ]);
    }
}


<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Service;
use Carbon\Carbon;

class PurchaseInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some suppliers
        $suppliers = Supplier::take(3)->get();
        if ($suppliers->isEmpty()) {
            $this->command->warn('No suppliers found. Please create suppliers first.');
            return;
        }

        // Get some products and services
        $products = Product::take(10)->get();
        $services = Service::take(5)->get();

        $currencies = ['TRY', 'USD', 'EUR'];
        $vatStatuses = ['included', 'excluded'];
        $paymentStatuses = [true, false];

        for ($i = 1; $i <= 5; $i++) {
            $supplier = $suppliers->random();
            $currency = $currencies[array_rand($currencies)];
            $vatStatus = $vatStatuses[array_rand($vatStatuses)];
            $paymentCompleted = $paymentStatuses[array_rand($paymentStatuses)];
            
            // Generate invoice date (last 30 days)
            $invoiceDate = Carbon::now()->subDays(rand(1, 30));
            $dueDate = $invoiceDate->copy()->addDays(rand(15, 60));
            
            // Create invoice
            $invoiceNumber = 'ALI-' . date('Y') . '-' . str_pad(PurchaseInvoice::count() + $i, 6, '0', STR_PAD_LEFT);
            $invoice = PurchaseInvoice::create([
                'invoice_number' => $invoiceNumber,
                'supplier_id' => $supplier->id,
                'invoice_date' => $invoiceDate->format('Y-m-d'),
                'invoice_time' => $invoiceDate->format('H:i'),
                'due_date' => $dueDate->format('Y-m-d'),
                'currency' => $currency,
                'vat_status' => $vatStatus,
                'description' => "Test alış faturası #{$i} - {$supplier->name}",
                'subtotal' => 0, // Will be calculated
                'vat_amount' => 0, // Will be calculated
                'total_amount' => 0, // Will be calculated
                'payment_completed' => $paymentCompleted,
                'status' => $paymentCompleted ? 'paid' : 'pending',
                'created_at' => $invoiceDate,
                'updated_at' => $invoiceDate,
            ]);

            // Create 2-4 items per invoice
            $itemCount = rand(2, 4);
            $subtotal = 0;
            $vatAmount = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                // Randomly choose product or service
                $isProduct = rand(0, 1);
                
                if ($isProduct && $products->isNotEmpty()) {
                    $item = $products->random();
                    $name = $item->name;
                    $price = $item->purchase_price ?: $item->sale_price ?: rand(50, 500);
                    $vatRate = $item->vat_rate ?: 20;
                } elseif ($services->isNotEmpty()) {
                    $item = $services->random();
                    $name = $item->name;
                    $price = $item->price ?: rand(50, 500);
                    $vatRate = $item->vat_rate ?: 20;
                } else {
                    // Fallback if no products/services
                    $name = "Test Ürün {$j}";
                    $price = rand(50, 500);
                    $vatRate = [0, 1, 10, 20][array_rand([0, 1, 10, 20])];
                }

                $quantity = rand(1, 10);
                $discountRate = rand(0, 20);
                
                // Calculate line total
                $lineTotal = $quantity * $price;
                $discountAmount = $lineTotal * ($discountRate / 100);
                $lineTotalAfterDiscount = $lineTotal - $discountAmount;
                
                $itemVatAmount = $lineTotalAfterDiscount * ($vatRate / 100);
                $finalLineTotal = $lineTotalAfterDiscount + $itemVatAmount;

                $subtotal += $lineTotalAfterDiscount;
                $vatAmount += $itemVatAmount;

                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'product_service_name' => $name,
                    'description' => "Test açıklama - {$name}",
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'unit_currency' => $currency,
                    'tax_rate' => $vatRate,
                    'discount_rate' => $discountRate,
                    'line_total' => $finalLineTotal,
                    'sort_order' => $j,
                    'created_at' => $invoiceDate,
                    'updated_at' => $invoiceDate,
                ]);
            }

            // Update invoice totals
            $totalAmount = $subtotal + $vatAmount;
            $invoice->update([
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'total_amount' => $totalAmount,
            ]);

            // Update supplier balance if payment not completed
            if (!$paymentCompleted) {
                switch ($currency) {
                    case 'TRY':
                        $supplier->increment('balance_try', $totalAmount);
                        break;
                    case 'USD':
                        $supplier->increment('balance_usd', $totalAmount);
                        break;
                    case 'EUR':
                        $supplier->increment('balance_eur', $totalAmount);
                        break;
                }
                $supplier->increment('balance', $totalAmount);
            }

            $this->command->info("Created purchase invoice: {$invoice->invoice_number} - {$supplier->name} - {$totalAmount} {$currency}");
        }

        $this->command->info('Purchase invoices seeded successfully!');
    }
}
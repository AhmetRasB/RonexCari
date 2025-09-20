<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('expense_invoice_items')) {
            Schema::create('expense_invoice_items', function (Blueprint $table) {
                $table->id();
                // Only add foreign key if expense_invoices table exists
                if (Schema::hasTable('expense_invoices')) {
                    $table->foreignId('expense_invoice_id')->constrained()->onDelete('cascade');
                } else {
                    $table->unsignedBigInteger('expense_invoice_id');
                }
                $table->string('item_type'); // 'product' or 'service'
                $table->unsignedBigInteger('item_id'); // product_id or service_id
                $table->string('item_name');
                $table->text('description')->nullable();
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 15, 2);
                $table->decimal('tax_rate', 5, 2)->default(0);
                $table->decimal('tax_amount', 15, 2)->default(0);
                $table->decimal('total_amount', 15, 2);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_invoice_items');
    }
};

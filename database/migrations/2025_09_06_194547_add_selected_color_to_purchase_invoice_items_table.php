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
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_invoice_items', 'selected_color')) {
                $table->string('selected_color')->nullable()->after('description');
            }
            if (!Schema::hasColumn('purchase_invoice_items', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('selected_color');
            }
            if (!Schema::hasColumn('purchase_invoice_items', 'product_type')) {
                $table->string('product_type')->nullable()->after('product_id'); // 'product', 'series', 'service'
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['selected_color', 'product_id', 'product_type']);
        });
    }
};


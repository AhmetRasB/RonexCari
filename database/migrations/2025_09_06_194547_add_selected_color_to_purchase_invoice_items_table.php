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
            $table->string('selected_color')->nullable()->after('description');
            $table->unsignedBigInteger('product_id')->nullable()->after('selected_color');
            $table->string('product_type')->nullable()->after('product_id'); // 'product', 'series', 'service'
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


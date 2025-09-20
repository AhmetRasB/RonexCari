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
        // Update existing records with SKU codes if they are null
        $products = \DB::table('products')->whereNull('sku')->get();
        foreach ($products as $index => $product) {
            \DB::table('products')
                ->where('id', $product->id)
                ->update(['sku' => 'PRD-' . str_pad($product->id, 6, '0', STR_PAD_LEFT)]);
        }
        
        // Add unique constraint to sku if it doesn't exist
        if (!Schema::hasIndex('products', 'products_sku_unique')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unique('sku');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['sku']);
        });
    }
};

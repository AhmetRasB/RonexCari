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
        // Update existing records with product codes if they are null
        $products = \DB::table('products')->whereNull('product_code')->get();
        foreach ($products as $index => $product) {
            \DB::table('products')
                ->where('id', $product->id)
                ->update(['product_code' => 'PRD-' . str_pad($product->id, 6, '0', STR_PAD_LEFT)]);
        }
        
        // Add unique constraint to product_code
        Schema::table('products', function (Blueprint $table) {
            $table->unique('product_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['product_code']);
        });
    }
};

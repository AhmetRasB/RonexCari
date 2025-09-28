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
        Schema::create('product_series_color_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_series_id')->constrained()->onDelete('cascade');
            $table->string('color');
            $table->integer('stock_quantity')->default(0);
            $table->integer('critical_stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_series_color_variants');
    }
};

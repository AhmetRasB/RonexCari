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
        Schema::create('product_series_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_series_id')->constrained()->onDelete('cascade');
            $table->string('size'); // Beden (XS, S, M, L, XL, XXL, 28, 30, 32, vs.)
            $table->integer('quantity_per_series')->default(1); // Her seride kaç adet
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_series_items');
    }
};

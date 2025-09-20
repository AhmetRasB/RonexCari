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
        Schema::create('product_series', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Seri adı (örn: "Oduncu Gömleği Serisi")
            $table->string('sku')->nullable(); // Seri SKU
            $table->text('description')->nullable(); // Seri açıklaması
            $table->string('category')->nullable(); // Kategori
            $table->string('brand')->nullable(); // Marka
            $table->decimal('cost', 10, 2)->default(0); // Maliyet
            $table->decimal('price', 10, 2)->default(0); // Satış fiyatı
            $table->string('image')->nullable(); // Görsel
            $table->integer('series_size'); // Seri boyutu (5, 6, 7, vs.)
            $table->integer('stock_quantity')->default(0); // Seri stok miktarı
            $table->integer('critical_stock')->default(0); // Kritik stok
            $table->boolean('is_active')->default(true); // Aktif mi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_series');
    }
};

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
        Schema::create('product_color_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('color'); // Renk adı
            $table->string('color_code')->nullable(); // Renk kodu (#FF0000)
            $table->integer('stock_quantity')->default(0); // Bu renkteki stok
            $table->integer('critical_stock')->default(0); // Bu renkteki kritik stok
            $table->string('image')->nullable(); // Bu renge özel görsel
            $table->boolean('is_active')->default(true); // Aktif mi
            $table->timestamps();
            
            // Aynı ürün için aynı renk sadece bir kez olabilir
            $table->unique(['product_id', 'color']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_color_variants');
    }
};
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
        Schema::create('exchanges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('original_invoice_id'); // Değişim yapılan fatura
            $table->unsignedBigInteger('new_invoice_id'); // Yeni oluşturulan fatura
            $table->unsignedBigInteger('user_id');
            $table->decimal('exchange_amount', 15, 2)->default(0); // Fiyat farkı (pozitif ise ek ücret, negatif ise iade)
            $table->text('notes')->nullable(); // Değişim notları
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('original_invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('new_invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['original_invoice_id', 'new_invoice_id']);
        });

        Schema::create('exchange_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exchange_id');
            $table->unsignedBigInteger('original_item_id'); // Eski faturadaki item
            $table->unsignedBigInteger('new_item_id')->nullable(); // Yeni faturadaki item (null ise sadece geri alınmış)
            $table->decimal('original_amount', 15, 2); // Eski ürün tutarı
            $table->decimal('new_amount', 15, 2)->default(0); // Yeni ürün tutarı
            $table->decimal('difference', 15, 2)->default(0); // Fark (new_amount - original_amount)
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('exchange_id')->references('id')->on('exchanges')->onDelete('cascade');
            $table->foreign('original_item_id')->references('id')->on('invoice_items')->onDelete('cascade');
            $table->foreign('new_item_id')->references('id')->on('invoice_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_items');
        Schema::dropIfExists('exchanges');
    }
};

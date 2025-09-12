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
        Schema::create('check_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->enum('type', ['received', 'given']); // alınan, verilen
            $table->string('bank_name');
            $table->string('bank_branch')->nullable();
            $table->string('account_number')->nullable();
            $table->string('original_owner')->nullable();
            $table->string('check_number');
            $table->date('transaction_date');
            $table->date('due_date');
            $table->decimal('amount', 15, 2);
            $table->enum('currency', ['TRY', 'USD', 'EUR'])->default('TRY');
            $table->enum('status', ['portfolio', 'cashed', 'returned', 'cancelled'])->default('portfolio'); // portföyde, tahsil edildi, iade edildi, iptal edildi
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_bills');
    }
};

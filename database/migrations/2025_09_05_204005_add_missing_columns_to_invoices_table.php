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
        Schema::table('invoices', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('invoices', 'invoice_time')) {
                $table->time('invoice_time')->after('invoice_date');
            }
            if (!Schema::hasColumn('invoices', 'currency')) {
                $table->string('currency', 3)->default('TRY')->after('due_date');
            }
            if (!Schema::hasColumn('invoices', 'vat_status')) {
                $table->enum('vat_status', ['included', 'excluded'])->default('included')->after('currency');
            }
            if (!Schema::hasColumn('invoices', 'description')) {
                $table->text('description')->nullable()->after('vat_status');
            }
            if (!Schema::hasColumn('invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('invoices', 'additional_discount')) {
                $table->decimal('additional_discount', 15, 2)->default(0)->after('discount_amount');
            }
            if (!Schema::hasColumn('invoices', 'vat_amount')) {
                $table->decimal('vat_amount', 15, 2)->default(0)->after('additional_discount');
            }
            if (!Schema::hasColumn('invoices', 'payment_completed')) {
                $table->boolean('payment_completed')->default(false)->after('total_amount');
            }
            
            // Update existing columns if needed
            $table->decimal('subtotal', 15, 2)->default(0)->change();
            $table->decimal('total_amount', 15, 2)->default(0)->change();
            
            // Update status column to enum
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_time',
                'currency',
                'vat_status',
                'description',
                'discount_amount',
                'additional_discount',
                'vat_amount',
                'payment_completed'
            ]);
        });
    }
};
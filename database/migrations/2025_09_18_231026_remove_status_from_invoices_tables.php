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
        // Remove status column from invoices table
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        // Remove status column from purchase_invoices table
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add status column back to invoices table
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('status', ['draft', 'approved', 'paid', 'cancelled'])->default('draft');
        });
        
        // Add status column back to purchase_invoices table
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->enum('status', ['draft', 'approved', 'paid', 'cancelled'])->default('draft');
        });
    }
};
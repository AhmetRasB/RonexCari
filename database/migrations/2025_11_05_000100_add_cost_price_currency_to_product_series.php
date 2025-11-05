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
        Schema::table('product_series', function (Blueprint $table) {
            // Separate currencies for cost and price; default TRY for new rows
            $table->string('cost_currency')->default('TRY')->after('cost');
            $table->string('price_currency')->default('TRY')->after('price');
        });

        // Backfill existing rows: set both to TRY if null, or to prior generic currency if existed
        try {
            // If a generic currency column exists from previous versions, copy its value
            if (Schema::hasColumn('product_series', 'currency')) {
                \DB::table('product_series')
                    ->whereNotNull('currency')
                    ->update([
                        'cost_currency' => \DB::raw('currency'),
                        'price_currency' => \DB::raw('currency'),
                    ]);
            }
        } catch (\Throwable $e) {
            // Silent failback; fields already have defaults
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_series', function (Blueprint $table) {
            $table->dropColumn(['cost_currency', 'price_currency']);
        });
    }
};



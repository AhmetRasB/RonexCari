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
        // Add barcode/qr_code_value to product_color_variants if missing
        Schema::table('product_color_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_color_variants', 'barcode')) {
                $table->string('barcode')->nullable()->unique()->after('color');
            }
            if (!Schema::hasColumn('product_color_variants', 'qr_code_value')) {
                $table->string('qr_code_value')->nullable()->after('barcode');
            }
        });

        // Add barcode/qr_code_value to product_series_color_variants if missing
        Schema::table('product_series_color_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_series_color_variants', 'barcode')) {
                $table->string('barcode')->nullable()->unique()->after('color');
            }
            if (!Schema::hasColumn('product_series_color_variants', 'qr_code_value')) {
                $table->string('qr_code_value')->nullable()->after('barcode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_color_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_color_variants', 'qr_code_value')) {
                $table->dropColumn('qr_code_value');
            }
            if (Schema::hasColumn('product_color_variants', 'barcode')) {
                $table->dropColumn('barcode');
            }
        });

        Schema::table('product_series_color_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_series_color_variants', 'qr_code_value')) {
                $table->dropColumn('qr_code_value');
            }
            if (Schema::hasColumn('product_series_color_variants', 'barcode')) {
                $table->dropColumn('barcode');
            }
        });
    }
};



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
        Schema::table('products', function (Blueprint $table) {
            $table->string('permanent_barcode')->nullable()->unique()->after('barcode');
            $table->string('qr_code_value')->nullable()->after('permanent_barcode');
            $table->string('barcode_svg_path')->nullable()->after('qr_code_value');
            $table->string('qr_svg_path')->nullable()->after('barcode_svg_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['permanent_barcode', 'qr_code_value', 'barcode_svg_path', 'qr_svg_path']);
        });
    }
};



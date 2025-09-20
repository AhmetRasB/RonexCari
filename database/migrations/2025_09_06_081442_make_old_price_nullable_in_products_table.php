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
        // Check if old_price column exists before trying to modify it
        if (Schema::hasColumn('products', 'old_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('old_price', 10, 2)->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if old_price column exists before trying to modify it
        if (Schema::hasColumn('products', 'old_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('old_price', 10, 2)->nullable(false)->change();
            });
        }
    }
};

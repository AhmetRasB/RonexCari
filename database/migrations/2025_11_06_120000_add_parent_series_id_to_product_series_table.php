<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_series', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_series_id')->nullable()->after('account_id');
            $table->foreign('parent_series_id')->references('id')->on('product_series')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('product_series', function (Blueprint $table) {
            $table->dropForeign(['parent_series_id']);
            $table->dropColumn('parent_series_id');
        });
    }
};



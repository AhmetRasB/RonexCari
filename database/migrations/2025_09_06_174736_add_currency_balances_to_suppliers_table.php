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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->decimal('balance', 15, 2)->default(0)->after('notes');
            $table->decimal('balance_try', 15, 2)->default(0)->after('balance');
            $table->decimal('balance_usd', 15, 2)->default(0)->after('balance_try');
            $table->decimal('balance_eur', 15, 2)->default(0)->after('balance_usd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['balance', 'balance_try', 'balance_usd', 'balance_eur']);
        });
    }
};

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
            $table->decimal('paid_amount_try', 15, 2)->default(0)->after('balance_eur');
            $table->decimal('paid_amount_usd', 15, 2)->default(0)->after('paid_amount_try');
            $table->decimal('paid_amount_eur', 15, 2)->default(0)->after('paid_amount_usd');
            $table->date('last_payment_date')->nullable()->after('paid_amount_eur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['paid_amount_try', 'paid_amount_usd', 'paid_amount_eur', 'last_payment_date']);
        });
    }
};

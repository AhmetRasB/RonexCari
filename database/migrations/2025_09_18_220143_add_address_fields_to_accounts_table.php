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
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('name');
            $table->text('address')->nullable()->after('company_name');
            $table->string('city')->nullable()->after('address');
            $table->string('district')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('district');
            $table->string('phone')->nullable()->after('postal_code');
            $table->string('email')->nullable()->after('phone');
            $table->string('tax_number')->nullable()->after('email');
            $table->string('tax_office')->nullable()->after('tax_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'address',
                'city',
                'district',
                'postal_code',
                'phone',
                'email',
                'tax_number',
                'tax_office'
            ]);
        });
    }
};
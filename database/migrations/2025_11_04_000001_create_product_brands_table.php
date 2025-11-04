<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_brands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['account_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_brands');
    }
};



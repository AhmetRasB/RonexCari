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
        Schema::create('fixed_series_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('series_size')->unique(); // 5, 6, 7
            $table->json('sizes'); // ['XS', 'S', 'M', 'L', 'XL'] gibi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_series_settings');
    }
};

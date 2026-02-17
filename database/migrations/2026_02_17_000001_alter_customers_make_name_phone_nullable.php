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
        Schema::table('customers', function (Blueprint $table) {
            // Ad ve telefon alanlarını opsiyonel hale getir
            $table->string('name')->nullable()->change();
            $table->string('phone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Geri dönüşte eski davranışa döner (zorunlu alan)
            $table->string('name')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
        });
    }
};


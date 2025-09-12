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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        // Insert default roles
        DB::table('roles')->insert([
            [
                'name' => 'god_mode',
                'display_name' => 'God Mode',
                'description' => 'Full system access - Super Administrator',
                'permissions' => json_encode(['*']),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'admin',
                'display_name' => 'Admin',
                'description' => 'Full access to all modules',
                'permissions' => json_encode([
                    'dashboard', 'sales', 'purchases', 'products', 'finance', 
                    'expenses', 'reports', 'management'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'employee',
                'display_name' => 'Employee',
                'description' => 'Limited access - cannot see expenses module',
                'permissions' => json_encode([
                    'dashboard', 'sales', 'purchases', 'products', 'finance', 'reports'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
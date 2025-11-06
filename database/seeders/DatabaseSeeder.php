<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create or update admin user (idempotent)
        User::updateOrCreate(
            ['email' => 'admin@ronexcari.com'],
            [
            'name' => 'Admin User',
                'email_verified_at' => now(),
                // Keep existing password if present; otherwise set a default
                'password' => User::where('email', 'admin@ronexcari.com')->value('password') ?? bcrypt('password'),
            ]
        );

        // Create or update test user (idempotent)
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
            'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => User::where('email', 'test@example.com')->value('password') ?? bcrypt('password'),
            ]
        );

        // Seed roles first, then accounts, then users, then sample data
        $this->call([
            RoleSeeder::class,
            AccountSeeder::class, // Ronex1 ve Ronex2 hesapları
            AdminUserSeeder::class,
            EmployeeUserSeeder::class,
            SampleDataSeeder::class,
            CollectionSeeder::class,
            CheckBillSeeder::class,
            TestProductSeeder::class, // AccountSeeder'dan sonra çalışmalı (hesapları kullanıyor)
        ]);
    }
}

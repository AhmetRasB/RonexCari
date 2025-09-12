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
        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@ronexcari.com',
        ]);

        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed roles first, then users, then sample data
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            EmployeeUserSeeder::class,
            SampleDataSeeder::class,
            CollectionSeeder::class,
            CheckBillSeeder::class,
        ]);
    }
}

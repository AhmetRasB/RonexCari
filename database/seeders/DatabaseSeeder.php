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
        // Seed roles first, then accounts, then users, then sample data
        $this->call([
            RoleSeeder::class,
            AccountSeeder::class,
            AdminUserSeeder::class,
            EmployeeUserSeeder::class,
            SampleDataSeeder::class,
            CollectionSeeder::class,
            CheckBillSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class EmployeeUserSeeder extends Seeder
{
    /**
     * Run the database migrations.
     */
    public function run(): void
    {
        // Get employee role
        $employeeRole = Role::where('name', 'employee')->first();

        // Create Test Employee - Limited Access
        User::updateOrCreate(
            ['email' => 'employee@test.com'],
            [
                'name' => 'Test Employee',
                'email' => 'employee@test.com',
                'password' => Hash::make('password123'),
                'role_id' => $employeeRole->id,
                'email_verified_at' => now()
            ]
        );
    }
}
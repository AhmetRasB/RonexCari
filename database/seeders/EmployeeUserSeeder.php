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

        // Create Emre - Employee
        User::updateOrCreate(
            ['email' => 'emre@ronex.com.tr'],
            [
                'name' => 'Emre',
                'email' => 'emre@ronex.com.tr',
                'password' => Hash::make('emre2025'),
                'role_id' => $employeeRole->id,
                'email_verified_at' => now()
            ]
        );

        // Create Murat - Employee
        User::updateOrCreate(
            ['email' => 'murat@ronex.com.tr'],
            [
                'name' => 'Murat',
                'email' => 'murat@ronex.com.tr',
                'password' => Hash::make('murat2025'),
                'role_id' => $employeeRole->id,
                'email_verified_at' => now()
            ]
        );

        // Create Nedim - Employee
        User::updateOrCreate(
            ['email' => 'nedim@ronex.com.tr'],
            [
                'name' => 'Nedim',
                'email' => 'nedim@ronex.com.tr',
                'password' => Hash::make('nedim2025'),
                'role_id' => $employeeRole->id,
                'email_verified_at' => now()
            ]
        );
    }
}
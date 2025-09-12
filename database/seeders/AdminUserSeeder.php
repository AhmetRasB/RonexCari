<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database migrations.
     */
    public function run(): void
    {
        // Get roles
        $godModeRole = Role::where('name', 'god_mode')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $employeeRole = Role::where('name', 'employee')->first();

        // Update existing user to God Mode (AI assistant)
        $existingUser = User::where('email', 'ahmetrasimbayhan@gmail.com')->first();
        if ($existingUser) {
            $existingUser->update([
                'name' => 'AI Assistant (God Mode)',
                'role_id' => $godModeRole->id
            ]);
        }

        // Create Yasir - Admin
        User::updateOrCreate(
            ['email' => 'yasir@ronex.com.tr'],
            [
                'name' => 'Yasir',
                'email' => 'yasir@ronex.com.tr',
                'password' => Hash::make('firari44'),
                'role_id' => $adminRole->id,
                'email_verified_at' => now()
            ]
        );

        // Create Fatih - Admin  
        User::updateOrCreate(
            ['email' => 'fatih@ronex.com.tr'],
            [
                'name' => 'Fatih',
                'email' => 'fatih@ronex.com.tr',
                'password' => Hash::make('firari44'),
                'role_id' => $adminRole->id,
                'email_verified_at' => now()
            ]
        );
    }
}
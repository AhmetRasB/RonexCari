<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database migrations.
     */
    public function run(): void
    {
        // God Mode Role (for AI assistant)
        Role::updateOrCreate(
            ['name' => 'god_mode'],
            [
                'display_name' => 'God Mode',
                'description' => 'Full system access for AI assistant',
                'permissions' => ['*'] // All permissions
            ]
        );

        // Admin Role
        Role::updateOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'Full access to all modules',
                'permissions' => [
                    'dashboard',
                    'sales',
                    'purchases', 
                    'products',
                    'finance',
                    'expenses',
                    'reports',
                    'management'
                ]
            ]
        );

        // Employee Role
        Role::updateOrCreate(
            ['name' => 'employee'],
            [
                'display_name' => 'Employee',
                'description' => 'Limited access - cannot see management',
                'permissions' => [
                    'dashboard',
                    'sales',
                    'purchases',
                    'products', 
                    'finance',
                    'expenses',
                    'reports'
                ]
            ]
        );
    }
}
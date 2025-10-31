<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Ronex1',
                'code' => 'RONEX1',
                'description' => 'Ronex1 Hesap Tutma Sistemi',
                'is_active' => true,
            ],
            [
                'name' => 'Ronex2',
                'code' => 'RONEX2',
                'description' => 'Ronex2 Hesap Tutma Sistemi',
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $account) {
            \App\Models\Account::create($account);
        }
        
        $this->command->info('2 accounts created successfully!');
    }
}

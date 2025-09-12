<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'name' => 'Ahmet Yılmaz',
                'phone' => '0532 123 45 67',
                'emergency_contact_name' => 'Fatma Yılmaz',
                'emergency_contact_phone' => '0533 234 56 78',
                'monthly_salary' => 15000.00,
                'salary_day' => 5,
                'accumulated_salary' => 15000.00,
                'paid_amount' => 0.00,
                'is_active' => true,
            ],
            [
                'name' => 'Mehmet Demir',
                'phone' => '0534 345 67 89',
                'emergency_contact_name' => 'Ayşe Demir',
                'emergency_contact_phone' => '0535 456 78 90',
                'monthly_salary' => 18000.00,
                'salary_day' => 10,
                'accumulated_salary' => 18000.00,
                'paid_amount' => 5000.00,
                'last_payment_date' => now()->subDays(5),
                'is_active' => true,
            ],
            [
                'name' => 'Zeynep Kaya',
                'phone' => '0536 567 89 01',
                'emergency_contact_name' => 'Ali Kaya',
                'emergency_contact_phone' => '0537 678 90 12',
                'monthly_salary' => 12000.00,
                'salary_day' => 15,
                'accumulated_salary' => 12000.00,
                'paid_amount' => 12000.00,
                'last_payment_date' => now()->subDays(10),
                'is_active' => true,
            ],
            [
                'name' => 'Can Özkan',
                'phone' => '0538 789 01 23',
                'emergency_contact_name' => 'Elif Özkan',
                'emergency_contact_phone' => '0539 890 12 34',
                'monthly_salary' => 20000.00,
                'salary_day' => 20,
                'accumulated_salary' => 20000.00,
                'paid_amount' => 0.00,
                'is_active' => true,
            ],
            [
                'name' => 'Selin Arslan',
                'phone' => '0540 901 23 45',
                'emergency_contact_name' => 'Murat Arslan',
                'emergency_contact_phone' => '0541 012 34 56',
                'monthly_salary' => 16000.00,
                'salary_day' => 25,
                'accumulated_salary' => 16000.00,
                'paid_amount' => 8000.00,
                'last_payment_date' => now()->subDays(3),
                'is_active' => true,
            ],
            [
                'name' => 'Emre Çelik',
                'phone' => '0542 123 45 67',
                'emergency_contact_name' => 'Gül Çelik',
                'emergency_contact_phone' => '0543 234 56 78',
                'monthly_salary' => 14000.00,
                'salary_day' => 28,
                'accumulated_salary' => 0.00,
                'paid_amount' => 0.00,
                'is_active' => true,
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}
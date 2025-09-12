<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AccumulateEmployeeSalaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:accumulate-salaries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Accumulate monthly salaries for employees based on their salary day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting employee salary accumulation...');
        
        $today = now();
        $currentDay = $today->day;
        
        // Get all active employees whose salary day matches today
        $employees = Employee::where('is_active', true)
            ->where('salary_day', $currentDay)
            ->get();
        
        if ($employees->isEmpty()) {
            $this->info("No employees found with salary day {$currentDay}");
            return;
        }
        
        $accumulatedCount = 0;
        
        foreach ($employees as $employee) {
            try {
                // Check if salary was already accumulated this month
                $lastAccumulation = $employee->updated_at;
                $currentMonth = $today->format('Y-m');
                $lastAccumulationMonth = $lastAccumulation->format('Y-m');
                
                // Only accumulate if not already done this month
                if ($currentMonth !== $lastAccumulationMonth || $employee->accumulated_salary == 0) {
                    $employee->increment('accumulated_salary', $employee->monthly_salary);
                    $employee->touch(); // Update updated_at timestamp
                    
                    $accumulatedCount++;
                    
                    $this->line("Accumulated salary for {$employee->name}: {$employee->monthly_salary} ₺");
                    
                    Log::info('Employee salary accumulated', [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->name,
                        'salary_amount' => $employee->monthly_salary,
                        'accumulated_total' => $employee->accumulated_salary
                    ]);
                } else {
                    $this->line("Salary already accumulated this month for {$employee->name}");
                }
            } catch (\Exception $e) {
                $this->error("Failed to accumulate salary for {$employee->name}: " . $e->getMessage());
                
                Log::error('Employee salary accumulation failed', [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("Salary accumulation completed. {$accumulatedCount} employees processed.");
        
        Log::info('Employee salary accumulation job completed', [
            'processed_count' => $accumulatedCount,
            'total_employees' => $employees->count(),
            'date' => $today->toDateString()
        ]);
    }
}

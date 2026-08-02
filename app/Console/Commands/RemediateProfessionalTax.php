<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\SalaryGroup;
use App\Models\ESStatutory;

class RemediateProfessionalTax extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary:remediate-pt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remediate missing Professional Tax statutory links for eligible active employee salaries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Professional Tax Data Remediation...');
        $fixed = 0;

        foreach (Employee::all() as $emp) {
            $es = EmployeeSalary::where('employee_id', $emp->id)->orderBy('effective_from', 'desc')->first();
            if (!$es) continue;

            // Check if PT is in employee salary group
            $hasPTInGroup = SalaryGroup::find($es->salary_group_id)->statutories()->where('statutory_compliances.id', 3)->exists();
            if (!$hasPTInGroup) continue;

            // Check if employee already has PT in es_statutories
            $hasPTInES = ESStatutory::where('employee_salary_id', $es->id)->where('statutory_compliance_id', 3)->exists();

            // Determine target condition ID for Maharashtra
            $targetCondId = null;
            if ($emp->gender == 'Female' && $es->gross_pay > 25000) {
                $targetCondId = 8;
            } elseif ($emp->gender == 'Male' && $es->gross_pay > 10000) {
                $targetCondId = 6;
            } elseif ($emp->gender == 'Male' && $es->gross_pay >= 7501 && $es->gross_pay <= 10000) {
                $targetCondId = 5;
            }

            if ($targetCondId && !$hasPTInES) {
                $this->line("Fixing {$emp->employee_code} ({$emp->first_name} {$emp->last_name}) | SalaryID: {$es->id} | Target Condition: {$targetCondId}");
                ESStatutory::create([
                    'employee_salary_id' => $es->id,
                    'salary_group_id' => $es->salary_group_id,
                    'statutory_compliance_id' => 3,
                    'statutory_compliance_condition_id' => $targetCondId,
                ]);
                $fixed++;
            }
        }

        $this->info("Remediation completed successfully. Total records fixed: {$fixed}");
        return Command::SUCCESS;
    }
}

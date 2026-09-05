<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'ctc',
        'basic_pay',
        'gross_pay',
        'total_earning',
        'overtime_earning',
        'reimbursement',
        'loan_disbursal',
        'gross_salary',
        'gross_deduction',
        'net_payable_amount',
        'is_email_sent',
        'email_sent_at',
    ];

    protected $casts = [
        'is_email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
    ];

    public function payroll(){
        return $this->belongsTo(Payroll::class);
    }

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function payroll_employee_attendances(){
        return $this->hasOne(PayrollEmployeeAttendance::class);
    }

    public function payroll_employee_breakups(){
        return $this->hasMany(PayrollEmployeeBreakup::class)->orderBy('name_in_payslip', 'asc');
    }

    public function payroll_employee_earnings(){
        return $this->hasMany(PayrollEmployeeBreakup::class)->where(function($q){
            $q
            ->where('breakupable_type', 'App\Models\PayrollEmployeeAttendance')
            ->orWhere('breakupable_type', 'App\Models\Earning')
            ->orWhere('breakupable_type', 'App\Models\ReimbursementApproval')
            ->orWhere('breakupable_type', 'App\Models\EmployeeSalary')
            ->orWhere(function($qq){
                $qq
                ->where('breakupable_type', 'App\Models\LoanAndAdvanceApproval')
                ->where('name_in_payslip', 'Loan');
            });
        })->orderBy('name_in_payslip', 'asc');
    }

    public function payroll_employee_deductions(){
        return $this->hasMany(PayrollEmployeeBreakup::class)->where(function($q){
            $q
            ->where('breakupable_type', 'App\Models\FineApproval')
            ->orWhere('breakupable_type', 'App\Models\ServicesComponent')
            ->orWhere('breakupable_type', 'App\Models\StatutoryComplianceCondition')
            ->orWhere(function($qq){
                $qq
                ->where('breakupable_type', 'App\Models\LoanAndAdvanceApproval')
                ->where('name_in_payslip', 'Loan EMI');
            });
        })->orderBy('name_in_payslip', 'asc');
    }

    protected static function booted () {
        static::deleting(function(PayrollEmployee $employee) {
            $employee->payroll_employee_attendances()->delete();
            $employee->payroll_employee_breakups()->delete();
        });
    }

    protected $appends = ["amount_str"];

    public function getAmountStrAttribute(){
        return $this->displaywords($this->net_payable_amount);
    }

    public function displaywords($number){
        $isNegative = $number < 0;
        $number = abs((float) $number);
        $num = (int) floor($number);
        $number_after_decimal = round($number - $num, 2) * 100;
        
        if ($num == 0) {
            $words = 'Zero';
        } else {
            $words = $this->convertNumberToWordsIndian($num);
        }

        if ($number_after_decimal > 0) {
            $words .= ' Point ' . $this->convertNumberToWordsIndian((int)$number_after_decimal);
        }

        $result = trim(preg_replace('/\s+/', ' ', $words));
        return $isNegative ? 'Minus ' . $result : $result;
    }

    private function convertNumberToWordsIndian($number) {
        $number = (int) $number;
        if ($number <= 0) {
            return '';
        }

        $words = array(
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
            20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty',
            60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        );

        if ($number < 21) {
            return $words[$number] ?? '';
        }
        if ($number < 100) {
            $tens = floor($number / 10) * 10;
            $units = $number % 10;
            return ($words[$tens] ?? '') . ($units > 0 ? ' ' . ($words[$units] ?? '') : '');
        }
        if ($number < 1000) {
            $rem = $number % 100;
            return ($words[floor($number / 100)] ?? '') . ' Hundred' . ($rem > 0 ? ' ' . $this->convertNumberToWordsIndian($rem) : '');
        }
        if ($number < 100000) {
            $rem = $number % 1000;
            return $this->convertNumberToWordsIndian(floor($number / 1000)) . ' Thousand' . ($rem > 0 ? ' ' . $this->convertNumberToWordsIndian($rem) : '');
        }
        if ($number < 10000000) {
            $rem = $number % 100000;
            return $this->convertNumberToWordsIndian(floor($number / 100000)) . ' Lakh' . ($rem > 0 ? ' ' . $this->convertNumberToWordsIndian($rem) : '');
        }
        $rem = $number % 10000000;
        return $this->convertNumberToWordsIndian(floor($number / 10000000)) . ' Crore' . ($rem > 0 ? ' ' . $this->convertNumberToWordsIndian($rem) : '');
    }
    
}

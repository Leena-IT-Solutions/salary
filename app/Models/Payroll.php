<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_year_id',
        'payroll_name',
        'from',
        'to',
        'working_days',
        'actual_days',
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
    ];

    public function payroll_employees(){
        return $this->hasMany(PayrollEmployee::class);
    }

    protected $appends = ["amount_str"];

    public function getAmountStrAttribute(){
        return $this->displaywords($this->net_payable_amount);
    }

    public function displaywords($number){
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

        return trim(preg_replace('/\s+/', ' ', $words));
    }

    private function convertNumberToWordsIndian($number) {
        $words = array(
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
            20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty',
            60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        );

        if ($number < 21) {
            return $words[$number];
        }
        if ($number < 100) {
            return $words[floor($number / 10) * 10] . ' ' . $words[$number % 10];
        }
        if ($number < 1000) {
            return $words[floor($number / 100)] . ' Hundred ' . $this->convertNumberToWordsIndian($number % 100);
        }
        if ($number < 100000) {
            return $this->convertNumberToWordsIndian(floor($number / 1000)) . ' Thousand ' . $this->convertNumberToWordsIndian($number % 1000);
        }
        if ($number < 10000000) {
            return $this->convertNumberToWordsIndian(floor($number / 100000)) . ' Lakh ' . $this->convertNumberToWordsIndian($number % 100000);
        }
        return $this->convertNumberToWordsIndian(floor($number / 10000000)) . ' Crore ' . $this->convertNumberToWordsIndian($number % 10000000);
    }

    protected static function booted () {
        static::deleting(function(Payroll $payroll) {
            $employees = $payroll->payroll_employees()->get();
            foreach($employees as $employee){
                $employee->payroll_employee_attendances()->delete();
                $employee->payroll_employee_breakups()->delete();
            }
            $payroll->payroll_employees()->delete();
        });
    }
}

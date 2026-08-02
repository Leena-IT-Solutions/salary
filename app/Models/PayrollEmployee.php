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
        $number_after_decimal = round($number - ($num = floor($number)), 2) * 100;

        // Check if there is any number after decimal
        $amt_hundred = null;
        $count_length = strlen($num);
        $x = 0;
        $string = array();
        $change_words = array(
            0 => 'Zero', 1 => 'One', 2 => 'Two',
            3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
            13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
            19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
            40 => 'Fourty', 50 => 'Fifty', 60 => 'Sixty',
            70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        );
        $here_digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        while ($x < $count_length) {
            $get_divider = ($x == 2) ? 10 : 100;
            $number = floor($num % $get_divider);
            $num = floor($num / $get_divider);
            $x += $get_divider == 10 ? 1 : 2;
            if ($number) {
                $add_plural = (($counter = count($string)) && $number > 9) ? 's' : null;
                $amt_hundred = ($counter == 1 && $string[0]) ? ' and ' : null;
                $string[] = ($number < 21) ? $change_words[$number] . ' ' . $here_digits[$counter] . $add_plural . '
        ' . $amt_hundred : $change_words[floor($number / 10) * 10] . ' ' . $change_words[$number % 10] . '
        ' . $here_digits[$counter] . $add_plural . ' ' . $amt_hundred;
            } else $string[] = null;
        }
        $implode_to_Words = implode('', array_reverse($string));
        $get_word_after_point = ($number_after_decimal > 0) ? "Point " . ($change_words[$number_after_decimal / 10] . "
            " . $change_words[$number_after_decimal % 10]) : '';
        return ($implode_to_Words ? $implode_to_Words : ' ') . $get_word_after_point;
    }
    
}

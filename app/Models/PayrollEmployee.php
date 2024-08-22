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
    ];

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
        $words = array('0' => '', '1' => 'one', '2' => 'two',
        '3' => 'three', '4' => 'four', '5' => 'five', '6' => 'six',
        '7' => 'seven', '8' => 'eight', '9' => 'nine',
        '10' => 'ten', '11' => 'eleven', '12' => 'twelve',
        '13' => 'thirteen', '14' => 'fourteen',
        '15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
        '18' => 'eighteen', '19' =>'nineteen', '20' => 'twenty',
        '30' => 'thirty', '40' => 'forty', '50' => 'fifty',
        '60' => 'sixty', '70' => 'seventy',
        '80' => 'eighty', '90' => 'ninety');
        $digits = array('', '', 'hundred', 'thousand', 'lakh', 'crore');
    
        $number = explode(".", $number);
        $result = array("","");
        $j =0;
        foreach($number as $val){
            // loop each part of number, right and left of dot
            for($i=0;$i<strlen($val);$i++){
                // look at each part of the number separately  [1] [5] [4] [2]  and  [5] [8]
    
                $numberpart = str_pad($val[$i], strlen($val)-$i, "0", STR_PAD_RIGHT); // make 1 => 1000, 5 => 500, 4 => 40 etc.
                if($numberpart <= 20){ // if it's below 20 the number should be one word
                    $numberpart = 1*substr($val, $i,2); // use two digits as the word
                    $i++; // increment i since we used two digits
                    $result[$j] .= $words[$numberpart] ." ";
                }else{
                    //echo $numberpart . "<br>\n"; //debug
                    if($numberpart > 90){  // more than 90 and it needs a $digit.
                        $result[$j] .= $words[$val[$i]] . " " . $digits[strlen($numberpart)-1] . " "; 
                    }else if($numberpart != 0){ // don't print zero
                        $result[$j] .= $words[str_pad($val[$i], strlen($val)-$i, "0", STR_PAD_RIGHT)] ." ";
                    }
                }
            }
            $j++;
        }
        if(trim($result[0]) != "") echo $result[0] . "Rupees ";
        if($result[1] != "") echo $result[1] . "Paise";
        echo " Only";
    }
    
}

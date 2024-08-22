<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanAndAdvanceApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'application_date',
        'disbursed_date',
        'close_date',
        'loan_amount',
        'emi_amount',
        'rate_of_interest',
        'tenure',
        'status',
        'reason',
        'is_pause',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function breakup(){
        return $this->morphOne(PayrollEmployeeBreakup::class, 'breakupable');
    }
}

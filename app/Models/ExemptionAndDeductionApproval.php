<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExemptionAndDeductionApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'exe_and_ded_component_id',
        'app_date',
        'amount',
        'status',
        'note',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function exe_and_ded_component(){
        return $this->belongsTo(ExeAndDedComponent::class);
    }

    public function breakup(){
        return $this->morphOne(PayrollEmployeeBreakup::class, 'breakupable');
    }
}

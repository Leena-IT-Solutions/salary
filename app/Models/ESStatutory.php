<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ESStatutory extends Model
{
    use HasFactory;

    protected $fillable = [
        "employee_salary_id",
        "salary_group_id",
        "statutory_compliance_id",
        "statutory_compliance_condition_id"
    ];

    public function employee_salary(){
        return $this->belongsTo(EmployeeSalary::class);
    }

    public function salary_group(){
        return $this->belongsTo(SalaryGroup::class);
    }

    public function statutory_compliance(){
        return $this->belongsTo(StatutoryCompliance::class);
    }

    public function statutory_compliance_condition(){
        return $this->belongsTo(StatutoryComplianceCondition::class);
    }
}

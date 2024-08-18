<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutoryCompliance extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheme_name',
        'abbreviation',
        'registration_number',
        'is_active',
        'is_part_of_salary',
        'is_pro_rata',
    ];

    public function salary_groups(){
        return $this->morphToMany(SalaryGroup::class, 'salary_groupable');
    }

    public function statutory_compliance_conditions(){
        return $this->hasMany(StatutoryComplianceCondition::class);
    }

    protected $appends = ['employee_contribution', 'employer_contribution', 'is'];

    public function getEmployeeContributionAttribute(){
        return 0;
    }

    public function getEmployerContributionAttribute(){
        return 0;
    }

    public function getIsAttribute(){
        return false;
    }
}

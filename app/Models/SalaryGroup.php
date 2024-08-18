<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_group_name',
        'note',
        'multiplier',
        'is_active',
    ];

    public function earnings(){
        return $this->morphedByMany(Earning::class, 'salary_groupable');
    }

    public function services(){
        return $this->morphedByMany(ServicesComponent::class, 'salary_groupable');
    }

    public function reimbursements(){
        return $this->morphedByMany(ReimbursementComponent::class, 'salary_groupable');
    }

    public function statutories(){
        return $this->morphedByMany(StatutoryCompliance::class, 'salary_groupable');
    }
}

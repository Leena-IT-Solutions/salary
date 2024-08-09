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

    public function statutory_compliance_conditions(){
        return $this->hasMany(StatutoryComplianceCondition::class);
    }
}

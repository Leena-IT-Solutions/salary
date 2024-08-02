<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    use HasFactory;

    protected $fillable = [
        'earning_type_id',
        'name',
        'name_in_payslip',
        'calculation',
        'pay_time',
        'value',
        'is_fbp',
        'is_fbp_restricted',
        'is_active',
        'is_part_of_salary',
        'is_taxable',
        'is_pro_rata',
        'is_epf',
        'is_esi',
        'is_in_payslip',
    ];
}

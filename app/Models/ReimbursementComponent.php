<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReimbursementComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'reimbursement_type_id',
        'name',
        'name_in_payslip',
        'value',
        'is_active',
        'is_annual',
    ];
}

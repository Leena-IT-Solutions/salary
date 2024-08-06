<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExeAndDedComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'exe_and_ded_type_id',
        'name',
        'name_in_payslip',
        'calculation',
        'value',
        'is_active',
    ];
}

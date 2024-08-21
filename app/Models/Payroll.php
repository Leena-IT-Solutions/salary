<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_year_id',
        'payroll_name',
        'from',
        'to',
        'working_days',
        'actual_days',
        'gross_pay',
        'net_pay',
    ];
}

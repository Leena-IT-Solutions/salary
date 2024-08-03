<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExemptionAndDeductionApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'app_date',
        'amount',
        'status',
        'note',
    ];
}

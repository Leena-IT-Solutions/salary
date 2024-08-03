<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EMIData extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'emi_amount',
        'repay_date',
    ];
}

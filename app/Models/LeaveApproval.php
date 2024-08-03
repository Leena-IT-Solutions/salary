<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_master_id',
        'from',
        'to',
        'reason',
        'status',
        'is_halfday',
        'is_lop',
    ];

}

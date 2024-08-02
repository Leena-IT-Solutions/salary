<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_group_id',
        'from',
        'to'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveGroupHead extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_group_id',
        'leave_master_id',
        'no_of_leaves'
    ];
}

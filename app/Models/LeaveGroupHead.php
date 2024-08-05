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

    public function leave_group(){
        return $this->belongsTo(LeaveGroup::class);
    }

    public function leave_master(){
        return $this->belongsTo(LeaveMaster::class);
    }

}

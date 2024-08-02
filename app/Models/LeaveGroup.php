<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'total_leaves'
    ];

    public function lgh(){
        return $this->hasMany(LeaveGroupHead::class);
    }
}

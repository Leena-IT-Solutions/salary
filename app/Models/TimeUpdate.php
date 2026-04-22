<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'employee_shift_id',
        'on_date',
        'in_time',
        'out_time',
        'reason',
        'status',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}

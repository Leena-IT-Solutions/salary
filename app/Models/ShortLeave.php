<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShortLeave extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'employee_shift_id',
        'on_date',
        'in_time',
        'out_time',
        'status',
        'is_lop',
        'reason',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}

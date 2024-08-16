<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnDuty extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'employee_shift_id',
        'on_date',
        'reason',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}

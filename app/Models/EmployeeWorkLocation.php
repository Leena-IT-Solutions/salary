<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeWorkLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'work_location_id',
        'from',
        'to',
    ];

    public function work_location(){
        return $this->belongsTo(WorkLocation::class);
    }
}

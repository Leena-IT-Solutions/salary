<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDesignation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'designation_id',
        'from',
        'to',
    ];

    public function designation(){
        return $this->belongsTo(Designation::class);
    }
}

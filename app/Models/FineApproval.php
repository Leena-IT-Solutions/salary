<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FineApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'app_date',
        'amount',
        'note',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function breakup(){
        return $this->morphOne(PayrollEmployeeBreakup::class, 'breakupable');
    }
}

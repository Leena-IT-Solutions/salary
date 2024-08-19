<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariablePayApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'earning_id',
        'app_date',
        'amount',
        'note',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function earning(){
        return $this->belongsTo(Earning::class);
    }
}

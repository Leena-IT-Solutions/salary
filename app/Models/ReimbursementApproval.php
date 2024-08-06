<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReimbursementApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'reimbursement_component_id',
        'app_date',
        'amount',
        'status',
        'note',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function reimbursement_component(){
        return $this->belongsTo(ReimbursementComponent::class);
    }
}

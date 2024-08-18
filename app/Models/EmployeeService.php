<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeService extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'services_component_id',
        'from',
        'to',
    ];

    public function services_component(){
        return $this->belongsTo(ServicesComponent::class);
    }
}

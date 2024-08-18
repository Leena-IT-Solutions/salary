<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeBank extends Model
{
    use HasFactory;

    protected $fillable = [
        "employee_id",
        "account_name",
        "account_number",
        "account_type",
        "bank_name",
        "branch",
        "ifsc",
    ];
}

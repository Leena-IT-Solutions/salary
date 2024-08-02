<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_name',
        'address',
        'city',
        'state',
        'pincode',
        'country',
        'phone',
        'email',
    ];
}

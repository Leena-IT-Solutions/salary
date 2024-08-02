<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'address',
        'city',
        'state',
        'pincode',
        'country',
        'phone',
        'email',
        'tan',
        'pan',
        'epf',
        'esic',
        'gst',
        'cin',
        'ptax',
        'logo'
    ];
}

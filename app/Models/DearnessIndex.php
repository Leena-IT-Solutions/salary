<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DearnessIndex extends Model
{
    use HasFactory;

    protected $fillable = [
        'index_amount',
        'on_date'
    ];
}

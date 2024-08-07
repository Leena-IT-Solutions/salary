<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_group_name',
        'note',
        'is_active',
    ];

    public function salary_group_data(){
        return $this->hasMany(SalaryGroupData::class);
    }
}

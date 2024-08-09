<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Earning;
use App\Models\ServicesComponent;
use App\Models\ReimbursementComponent;
use App\Models\StatutoryCompliance;

class SalaryGroupData extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_group_id',
        'common_id',
        'what',
    ];

    protected $appends = ['earning', 'service', 'reimbursement', 'statutory', 'monthly'];

    public function getEarningAttribute(){
        if($this->what == 'earning'){
            return Earning::find($this->common_id);
        }
        return null;
    }

    public function getServiceAttribute(){
        if($this->what == 'service'){
            return ServicesComponent::find($this->common_id);
        }
        return null;
    }

    public function getReimbursementAttribute(){
        if($this->what == 'reimbursement'){
            return ReimbursementComponent::find($this->common_id);
        }
        return null;
    }

    public function getStatutoryAttribute(){
        if($this->what == 'statutory'){
            return StatutoryCompliance::with('statutory_compliance_conditions')->find($this->common_id);
        }
        return null;
    }

    public function getMonthlyAttribute(){
        return 0;
    }

    public function getAnnuallyAttribute(){
        return 0;
    }

}

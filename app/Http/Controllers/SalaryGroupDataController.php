<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\SalaryGroup;
use App\Models\SalaryGroupData;
use App\Models\Earning;
use App\Models\ServicesComponent;
use App\Models\ReimbursementComponent;
use App\Models\StatutoryCompliance;

class SalaryGroupDataController extends Controller
{
    public function data($id){
        $salary_group = SalaryGroup::with('salary_group_data')->find($id);
        $earnings = Earning::where('is_active', true)->get();
        $services = ServicesComponent::where('is_active', true)->get();
        $reimbursements = ReimbursementComponent::where('is_active', true)->get();
        $statutory = StatutoryCompliance::where('is_active', true)->get();
        return view('salary_settings.data', compact('salary_group', 'earnings', 'services', 'reimbursements', 'statutory'));
    }

    public function update(Request $request){
        $is = SalaryGroupData::where('salary_group_id', $request->salary_group_id)
        ->where('common_id', $request->common_id)
        ->where('what', $request->what);
        if($is->exists()){
            $is->delete();
        } else {
            SalaryGroupData::create($request->all());
        }
        return $this->fetch($request->salary_group_id);
    }

    public function fetch($salary_group_id){
        return SalaryGroupData::where('salary_group_id', $salary_group_id)->get();
    }
}

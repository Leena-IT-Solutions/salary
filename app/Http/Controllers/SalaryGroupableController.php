<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalaryGroup;
use App\Models\Earning;
use App\Models\ServicesComponent;
use App\Models\ReimbursementComponent;
use App\Models\StatutoryCompliance;

class SalaryGroupableController extends Controller
{
    public function data($id){
        $salary_group = SalaryGroup::find($id);
        return view('salary_settings.data', compact('salary_group'));
    }

    public function earnings($id){
        return Earning::where('is_active', true)->with('salary_groups', function($q) use($id){
            $q->where('salary_group_id', $id);
        })->get();
    }

    public function services($id){
        return ServicesComponent::where('is_active', true)->with('salary_groups', function($q) use($id){
            $q->where('salary_group_id', $id);
        })->get();
    }

    public function reimbursements($id){
        return ReimbursementComponent::where('is_active', true)->with('salary_groups', function($q) use($id){
            $q->where('salary_group_id', $id);
        })->get();
    }

    public function statutories($id){
        return StatutoryCompliance::where('is_active', true)->with('salary_groups', function($q) use($id){
            $q->where('salary_group_id', $id);
        })->get();
    }

    public function update(Request $request){

        $item = null;
        switch($request->salary_groupable_type){
            case "earning":
                $item = Earning::find($request->salary_groupable_id);
            break;

            case "reimbursement":
                $item = ReimbursementComponent::find($request->salary_groupable_id);
                break;

            case "service":
                $item = ServicesComponent::find($request->salary_groupable_id);
                break;

            case "statutory":
                $item = StatutoryCompliance::find($request->salary_groupable_id);
                break;
        }

        $item->salary_groups()->toggle($request->salary_group_id);

        return $this->fetch($request->salary_group_id);
    }

    public function fetch($id){
        return $salary_group = SalaryGroup::with(['earnings', 'services', 'reimbursements', 'statutories'])->find($id);
    }
}

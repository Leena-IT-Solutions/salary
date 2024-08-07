<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\StatutoryCompliance;
use App\Models\StatutoryComplianceCondition;

class StatutoryComplianceConditionController extends Controller
{
    public function condition($id){
        $statutory_compliance = StatutoryCompliance::find($id);
        return view('salary_settings.condition', compact('statutory_compliance'));
    }

    public function fetch(Request $request, $id){
        $by = 'id';
        $order = 'desc';
        $key = null;
        $value = null;

        $by = isset($request->by) ? $request->by : $by;
        $order = isset($request->order) ? $request->order : $order;
        $key = isset($request->key) ? $request->key : $key;
        $value = isset($request->value) ? $request->value : $value;

        $items = StatutoryComplianceCondition::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->where('statutory_compliance_id', $id)->simplePaginate(25);
    }

    public function add(Request $request){
        $input = $request->all();
        return StatutoryComplianceCondition::create($input);
    }

    public function update(Request $request){
        $input = $request->all();
        return StatutoryComplianceCondition::find($request->id)->update($input);
    }

    public function delete(Request $request){
        return StatutoryComplianceCondition::find($request->id)->delete();
    }
}

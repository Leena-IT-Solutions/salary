<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ExeAndDedType;
use App\Models\ExeAndDedComponent;

class ExemptionAndDeductionController extends Controller
{
    public function exemption_and_deduction(){
        $types = ExeAndDedType::orderBy('name', 'asc')->get(['id as val', 'name as key']);
        return view('salary_settings.exemption_and_deduction', compact('types'));
    }

    public function fetch(Request $request){
        $by = 'id';
        $order = 'desc';
        $key = null;
        $value = null;

        $by = isset($request->by) ? $request->by : $by;
        $order = isset($request->order) ? $request->order : $order;
        $key = isset($request->key) ? $request->key : $key;
        $value = isset($request->value) ? $request->value : $value;

        $items = ExeAndDedComponent::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->simplePaginate(25);
    }

    public function add(Request $request){

        $input = $request->all();

        if(isset($request->custom_type)){
            $et = ExeAndDedType::create([
                'name' => $request->custom_type
            ]);
            $input['exe_and_ded_type_id'] = $et->id;
        }

        unset($input['custom_type']);

        return ExeAndDedComponent::create($input);
    }

    public function update(Request $request){

        $input = $request->all();

        if(isset($request->custom_type)){
            $et = ExeAndDedType::create([
                'name' => $request->custom_type
            ]);
            $input['exe_and_ded_type_id'] = $et->id;
        }

        unset($input['custom_type']);

        return ExeAndDedComponent::find($request->id)->update($input);
    }

    public function delete(Request $request){
        return ExeAndDedComponent::find($request->id)->delete();
    }
}

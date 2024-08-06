<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ReimbursementType;
use App\Models\ReimbursementComponent;

class ReimbursementController extends Controller
{
    public function reimbursement(){
        $types = ReimbursementType::orderBy('name', 'asc')->get(['id as val', 'name as key']);
        return view('salary_settings.reimbursement', compact('types'));
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

        $items = ReimbursementComponent::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->simplePaginate(25);
    }

    public function add(Request $request){

        $input = $request->all();

        if(isset($request->custom_type)){
            $et = ReimbursementType::create([
                'name' => $request->custom_type
            ]);
            $input['reimbursement_type_id'] = $et->id;
        }

        unset($input['custom_type']);

        return ReimbursementComponent::create($input);
    }

    public function update(Request $request){

        $input = $request->all();

        if(isset($request->custom_type)){
            $et = ReimbursementType::create([
                'name' => $request->custom_type
            ]);
            $input['reimbursement_type_id'] = $et->id;
        }

        unset($input['custom_type']);

        return ReimbursementComponent::find($request->id)->update($input);
    }

    public function delete(Request $request){
        return ReimbursementComponent::find($request->id)->delete();
    }
}

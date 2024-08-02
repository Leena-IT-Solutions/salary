<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EarningType;
use App\Models\Earning;

class EarningsController extends Controller
{
    public function earnings(){
        $earning_types = EarningType::orderBy('name', 'asc')->get(['id as val', 'name as key']);
        return view('salary_settings.earning_components', compact('earning_types'));
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

        $departments = Earning::orderBy($by, $order);
        if($key != null && $value != null){
            $departments = $departments->where($key, 'LIKE', '%'.$value.'%');
        }
        return $departments->simplePaginate(25);
    }

    public function add(Request $request){

        $input = $request->all();

        if(isset($request->custom_earning_type)){
            $et = EarningType::create([
                'name' => $request->custom_earning_type
            ]);
            $input['earning_type_id'] = $et->id;
        }

        unset($input['custom_earning_type']);

        return Earning::create($input);
    }

    public function update(Request $request){

        $input = $request->all();

        if(isset($request->custom_earning_type)){
            $et = EarningType::create([
                'name' => $request->custom_earning_type
            ]);
            $input['earning_type_id'] = $et->id;
        }

        unset($input['custom_earning_type']);

        return Earning::find($request->id)->update($input);
    }

    public function delete(Request $request){
        return Earning::find($request->id)->delete();
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ServicesType;
use App\Models\ServicesComponent;

class ServicesController extends Controller
{
    public function services(){
        $types = ServicesType::orderBy('name', 'asc')->get(['id as val', 'name as key']);
        return view('salary_settings.services', compact('types'));
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

        $items = ServicesComponent::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->simplePaginate(25);
    }

    public function add(Request $request){

        $input = $request->all();

        if(isset($request->custom_type)){
            $et = ServicesType::create([
                'name' => $request->custom_type
            ]);
            $input['services_type_id'] = $et->id;
        }

        unset($input['custom_type']);

        return ServicesComponent::create($input);
    }

    public function update(Request $request){

        $input = $request->all();

        if(isset($request->custom_type)){
            $et = ServicesType::create([
                'name' => $request->custom_type
            ]);
            $input['services_type_id'] = $et->id;
        }

        unset($input['custom_type']);

        return ServicesComponent::find($request->id)->update($input);
    }

    public function delete(Request $request){
        return ServicesComponent::find($request->id)->delete();
    }
}

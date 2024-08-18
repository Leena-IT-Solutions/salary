<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeService;

class EmployeeServicesController extends Controller
{
    public function fetch(Request $request){
        $by = 'id';
        $order = 'desc';
        $key = null;
        $value = null;

        $by = isset($request->by) ? $request->by : $by;
        $order = isset($request->order) ? $request->order : $order;
        $key = isset($request->key) ? $request->key : $key;
        $value = isset($request->value) ? $request->value : $value;

        $items = EmployeeService::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->with('services_component')->simplePaginate(25);
    }

    public function add(Request $request){
        return EmployeeService::create($request->all());
    }

    public function update(Request $request){
        return EmployeeService::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return EmployeeService::find($request->id)->delete();
    }
}

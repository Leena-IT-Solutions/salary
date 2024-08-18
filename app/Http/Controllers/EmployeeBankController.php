<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeBank;

class EmployeeBankController extends Controller
{

    public function fetch(Request $request, $id){
        $by = 'id';
        $order = 'desc';
        $key = null;
        $value = null;

        $by = isset($request->by) ? $request->by : $by;
        $order = isset($request->order) ? $request->order : $order;
        $key = isset($request->key) ? $request->key : $key;
        $value = isset($request->value) ? $request->value : $value;

        $items = EmployeeBank::where('employee_id', $id)->orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->simplePaginate(25);
    }

    public function add(Request $request){
        return EmployeeBank::create($request->all());
    }

    public function update(Request $request){
        return EmployeeBank::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return EmployeeBank::find($request->id)->delete();
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeAddress;

class EmployeeAddressController extends Controller
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

        $employee_addressess = EmployeeAddress::where('employee_id', $id)->orderBy($by, $order);
        if($key != null && $value != null){
            $employee_addressess = $employee_addressess->where($key, 'LIKE', '%'.$value.'%');
        }
        return $employee_addressess->simplePaginate(25);
    }

    public function add(Request $request){
        return EmployeeAddress::create($request->all());
    }

    public function update(Request $request){
        return EmployeeAddress::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return EmployeeAddress::find($request->id)->delete();
    }
}

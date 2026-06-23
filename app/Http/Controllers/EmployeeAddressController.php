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
        $validated = $request->validate([
            'employee_id' => 'required|integer',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
        ]);
        return EmployeeAddress::create($validated);
    }

    public function update(Request $request){
        $validated = $request->validate([
            'id' => 'required|exists:employee_addresses,id',
            'employee_id' => 'required|integer',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
        ]);
        $address = EmployeeAddress::find($request->id);
        $address->update($validated);
        return $address;
    }

    public function delete(Request $request){
        return EmployeeAddress::find($request->id)->delete();
    }
}

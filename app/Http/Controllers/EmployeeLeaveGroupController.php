<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeLeaveGroup;

class EmployeeLeaveGroupController extends Controller
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

        $data = EmployeeLeaveGroup::where('employee_id', $id)->orderBy($by, $order);
        if($key != null && $value != null){
            $data = $data->where($key, 'LIKE', '%'.$value.'%');
        }
        return $data->simplePaginate(25);
    }

    public function add(Request $request){
        return EmployeeLeaveGroup::create($request->all());
    }

    public function update(Request $request){
        return EmployeeLeaveGroup::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return EmployeeLeaveGroup::find($request->id)->delete();
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeWorkLocation;

class EmployeeWorkLocationController extends Controller
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

        $data = EmployeeWorkLocation::where('employee_id', $id)->orderBy($by, $order);
        if($key != null && $value != null){
            $data = $data->where($key, 'LIKE', '%'.$value.'%');
        }
        return $data->simplePaginate(25);
    }

    public function add(Request $request){
        return EmployeeWorkLocation::create($request->all());
    }

    public function update(Request $request){
        return EmployeeWorkLocation::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return EmployeeWorkLocation::find($request->id)->delete();
    }
}

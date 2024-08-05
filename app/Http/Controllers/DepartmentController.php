<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{

    public function departments(){
        return view('settings.departments');
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

        $items = Department::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->simplePaginate(25);
    }

    public function add(Request $request){
        return Department::create($request->all());
    }

    public function update(Request $request){
        return Department::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return Department::find($request->id)->delete();
    }

} 
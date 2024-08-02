<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkingShift;

class WorkingShiftController extends Controller
{

    public function working_shifts(){
        return view('settings.working_shifts');
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

        $working_shifts = WorkingShift::orderBy($by, $order);
        if($key != null && $value != null){
            $working_shifts = $working_shifts->where($key, 'LIKE', '%'.$value.'%');
        }
        return $working_shifts->simplePaginate(25);
    }

    public function add(Request $request){
        return WorkingShift::create($request->all());
    }

    public function update(Request $request){
        return WorkingShift::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return WorkingShift::find($request->id)->delete();
    }

}

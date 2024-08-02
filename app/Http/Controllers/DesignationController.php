<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Designation;

class DesignationController extends Controller
{
    public function designations(){
        return view('settings.designations');
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

        $designations = Designation::orderBy($by, $order);
        if($key != null && $value != null){
            $designations = $designations->where($key, 'LIKE', '%'.$value.'%');
        }
        return $designations->simplePaginate(25);
    }

    public function add(Request $request){
        return Designation::create($request->all());
    }

    public function update(Request $request){
        return Designation::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return Designation::find($request->id)->delete();
    }
}

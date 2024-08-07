<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\SalaryGroup;

class SalaryGroupController extends Controller
{
    public function salary_group(){
        return view('salary_settings.salary_group');
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

        $items = SalaryGroup::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->simplePaginate(25);
    }

    public function add(Request $request){
        $input = $request->all();
        return SalaryGroup::create($input);
    }

    public function update(Request $request){
        $input = $request->all();
        return SalaryGroup::find($request->id)->update($input);
    }

    public function delete(Request $request){
        return SalaryGroup::find($request->id)->delete();
    }
}

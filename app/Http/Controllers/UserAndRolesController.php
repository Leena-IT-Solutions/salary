<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserAndRolesController extends Controller
{
    public function user_and_roles(){
        return view("application_settings.user_and_roles");
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

        $items = User::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->simplePaginate(25);
    }

    public function add(Request $request){
        return User::create($request->all());
    }

    public function update(Request $request){
        return User::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return User::find($request->id)->delete();
    }
}

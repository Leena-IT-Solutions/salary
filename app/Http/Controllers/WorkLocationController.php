<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkLocation;
use App\Http\Requests\WorkLocationRequest;

class WorkLocationController extends Controller
{

    public function work_location(){
        return view('settings.work_locations');
    }
    
    public function fetch(){
        return WorkLocation::get();
    }

    public function add(WorkLocationRequest $request){
        return WorkLocation::create($request->all());
    }

    public function update(WorkLocationRequest $request){
        return WorkLocation::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return WorkLocation::find($request->id)->delete();
    }

}
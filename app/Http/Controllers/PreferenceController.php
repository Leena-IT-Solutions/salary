<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class PreferenceController extends Controller
{
    public function preference(){
        return view("application_settings.preference");
    }

    public function fetch(){
        return Setting::get();
    }

    public function save(Request $request){

        $setting = Setting::where('key', $request->key);
        $s = null;

        if($setting->exists()){
            $s = $setting->update($request->all());
        } else {
            $s = Setting::create($request->all());
        }
        return $s;
    }
}

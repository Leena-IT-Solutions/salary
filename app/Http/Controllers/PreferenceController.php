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
        $s = Setting::updateOrCreate(
            ['key' => $request->key],
            ['value' => $request->value]
        );
        return response()->json($s);
    }
}

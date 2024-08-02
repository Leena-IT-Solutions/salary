<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class CompanyProfileController extends Controller
{
    
    public function company_profile(){
        return view('settings.company_profile');
    }

    public function fetch(){
        return $company_profile = Auth::user()->company_profile;
    }

    public function update(Request $request){

        $user = Auth::user();

        $cp = $user->company_profile;

        if($cp == null){
            $user->company_profile()->create($request->all());
        } else {
            $user->company_profile()->update($request->all());
        }

        return $user->company_profile;

    }

    public function logo_upload(Request $request){

        $user = Auth::user();

        if($file = $request->file('logo')){
            $name = time().'_'.mt_rand(100000,999999).'_'.$file->getClientOriginalName();
            $file->move('images', $name);
            $path = "/images/" . $name;

            $data = [
                "logo" => $path
            ];

            $cp = $user->company_profile;
            if($cp == null){
                $user->company_profile()->create($data);
            } else {
                $old_logo = $cp->logo;

                if($old_logo != null || $old_logo != ""){
                    if(file_exists(substr($old_logo, 1))){
                        unlink(substr($old_logo, 1));
                    }
                }

                $user->company_profile()->update($data);
            }

            return $user->company_profile;
        }
    }

}

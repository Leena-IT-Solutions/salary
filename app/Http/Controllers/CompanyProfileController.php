<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompanyProfile;

class CompanyProfileController extends Controller
{
    
    public function company_profile(){
        return view('settings.company_profile');
    }

    public function fetch(){
        return CompanyProfile::get()->first();
    }

    public function update(Request $request){

        $input = $request->all();
        $input["user_id"] = 1;

        $cp = CompanyProfile::get()->first();

        if($cp == null){
            CompanyProfile::create($input);
        } else {
            CompanyProfile::get()->first()->update($input);
        }

        return CompanyProfile::get()->first();

    }

    public function logo_upload(Request $request){

        if($file = $request->file('logo')){
            $name = time().'_'.mt_rand(100000,999999).'_'.$file->getClientOriginalName();
            $file->move('images', $name);
            $path = "/images/" . $name;

            $data = [
                "logo" => $path
            ];

            $cp = CompanyProfile::get()->first();
            if($cp == null){
                CompanyProfile::create($data);
            } else {
                $old_logo = $cp->logo;

                if($old_logo != null || $old_logo != ""){
                    if(file_exists(substr($old_logo, 1))){
                        unlink(substr($old_logo, 1));
                    }
                }

                CompanyProfile::get()->first()->update($data);
            }

            return CompanyProfile::get()->first();
        }
    }

}

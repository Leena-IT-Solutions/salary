<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Companyprofile;

class CompanyProfileController extends Controller
{
    
    public function company_profile(){
        return view('settings.company_profile');
    }

    public function fetch(){
        return Companyprofile::get()->first();
    }

    public function update(Request $request){

        $cp = Companyprofile::get()->first();

        if($cp == null){
            Companyprofile::get()->first()->create($request->all());
        } else {
            Companyprofile::get()->first()->update($request->all());
        }

        return Companyprofile::get()->first();

    }

    public function logo_upload(Request $request){

        if($file = $request->file('logo')){
            $name = time().'_'.mt_rand(100000,999999).'_'.$file->getClientOriginalName();
            $file->move('images', $name);
            $path = "/images/" . $name;

            $data = [
                "logo" => $path
            ];

            $cp = Companyprofile::get()->first();
            if($cp == null){
                Companyprofile::get()->first()->create($data);
            } else {
                $old_logo = $cp->logo;

                if($old_logo != null || $old_logo != ""){
                    if(file_exists(substr($old_logo, 1))){
                        unlink(substr($old_logo, 1));
                    }
                }

                Companyprofile::get()->first()->update($data);
            }

            return Companyprofile::get()->first();
        }
    }

}

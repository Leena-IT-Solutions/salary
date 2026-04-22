<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeePhoto;
use Illuminate\Support\Facades\Storage;

class EmployeePhotoController extends Controller
{
    public function fetch(Request $request, $id){
        $by = 'id';
        $order = 'desc';
        $key = null;
        $value = null;

        $by = isset($request->by) ? $request->by : $by;
        $order = isset($request->order) ? $request->order : $order;
        $key = isset($request->key) ? $request->key : $key;
        $value = isset($request->value) ? $request->value : $value;

        $data = EmployeePhoto::where('employee_id', $id)->orderBy($by, $order);
        if($key != null && $value != null){
            $data = $data->where($key, 'LIKE', '%'.$value.'%');
        }
        return $data->simplePaginate(25);
    }

    public function add(Request $request){
        if($file = $request->file('media')){
            $name = time().'_'.mt_rand(100000,999999).'_'.$file->getClientOriginalName();
            $file->storeAs('employee', $name, 'public');
            $path = "/employee/" . $name;

            $data = [
                "media" => $path,
                "employee_id" => $request->employee_id
            ];

            return EmployeePhoto::create($data);
        }

        return null;
    }

    public function update(Request $request){
        if($file = $request->file('media')){
            $name = time().'_'.mt_rand(100000,999999).'_'.$file->getClientOriginalName();
            $file->storeAs('employee', $name, 'public');
            $path = "/employee/" . $name;

            $data = [
                "media" => $path,
                "employee_id" => $request->employee_id
            ];

            $photo = EmployeePhoto::find($request->id);
            
            if($photo != null) {
                $old_media = $photo->media;

                if($old_media != null && $old_media != ""){
                    $old_path = ltrim($old_media, '/');
                    if(Storage::disk('public')->exists($old_path)){
                        Storage::disk('public')->delete($old_path);
                    }
                }

                $photo->update($data);
            }

            return $photo;
        }

        return null;
    }

    public function delete(Request $request){
        $photo = EmployeePhoto::find($request->id);
            
        if($photo != null) {
            $old_media = $photo->media;

            if($old_media != null && $old_media != ""){
                $old_path = ltrim($old_media, '/');
                if(Storage::disk('public')->exists($old_path)){
                    Storage::disk('public')->delete($old_path);
                }
            }
            $photo->delete();
        }

        return true;
    }
}

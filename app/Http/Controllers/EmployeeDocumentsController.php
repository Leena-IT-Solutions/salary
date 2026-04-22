<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeDocument;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentsController extends Controller
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

        $data = EmployeeDocument::where('employee_id', $id)->orderBy($by, $order);
        if($key != null && $value != null){
            $data = $data->where($key, 'LIKE', '%'.$value.'%');
        }
        return $data->simplePaginate(25);
    }

    public function add(Request $request){
        if($file = $request->file('document')){
            $name = time().'_'.mt_rand(100000,999999).'_'.$file->getClientOriginalName();
            $file->storeAs('docs', $name, 'public');
            $path = "/docs/" . $name;

            $data = [
                "document_name" => $request->document_name,
                "employee_id" => $request->employee_id,
                "document" => $path
            ];

            return EmployeeDocument::create($data);
        }

        return null;
    }

    public function update(Request $request){
        $photo = EmployeeDocument::find($request->id);

        if($file = $request->file('document')){
            $name = time().'_'.mt_rand(100000,999999).'_'.$file->getClientOriginalName();
            $file->storeAs('docs', $name, 'public');
            $path = "/docs/" . $name;

            $data = [
                "document_name" => $request->document_name,
                "employee_id" => $request->employee_id,
                "document" => $path
            ];
            
            if($photo != null) {
                $old_document = $photo->document;

                if($old_document != null && $old_document != ""){
                    $old_path = ltrim($old_document, '/');
                    if(Storage::disk('public')->exists($old_path)){
                        Storage::disk('public')->delete($old_path);
                    }
                }

                $photo->update($data);
            }

            return $photo;

        } else {

            $data = [
                "document_name" => $request->document_name,
                "employee_id" => $request->employee_id,
            ];

            $photo->update($data);
            return $photo;

        }

        return null;
    }

    public function delete(Request $request){
        $photo = EmployeeDocument::find($request->id);
            
        if($photo != null) {
            $old_document = $photo->document;

            if($old_document != null && $old_document != ""){
                $old_path = ltrim($old_document, '/');
                if(Storage::disk('public')->exists($old_path)){
                    Storage::disk('public')->delete($old_path);
                }
            }
            $photo->delete();
        }

        return true;
    }
}

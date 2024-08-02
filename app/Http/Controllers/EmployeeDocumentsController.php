<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeDocument;

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
            $file->move('docs', $name);
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
            $file->move('docs', $name);
            $path = "/docs/" . $name;

            $data = [
                "document_name" => $request->document_name,
                "employee_id" => $request->employee_id,
                "document" => $path
            ];
            
            if($photo != null) {
                $old_document = $photo->document;

                if($old_document != null || $old_document != ""){
                    if(file_exists(substr($old_document, 1))){
                        unlink(substr($old_document, 1));
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

            if($old_document != null || $old_document != ""){
                if(file_exists(substr($old_document, 1))){
                    unlink(substr($old_document, 1));
                }
            }
        }

        return EmployeeDocument::find($request->id)->delete();
    }
}

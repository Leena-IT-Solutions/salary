<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeEducation;
use Illuminate\Support\Facades\Storage;

class EmployeeEducationController extends Controller
{
    public function fetch(Request $request, $id)
    {
        $by = 'id';
        $order = 'desc';
        $key = null;
        $value = null;

        $by = isset($request->by) ? $request->by : $by;
        $order = isset($request->order) ? $request->order : $order;
        $key = isset($request->key) ? $request->key : $key;
        $value = isset($request->value) ? $request->value : $value;

        $data = EmployeeEducation::where('employee_id', $id)->orderBy($by, $order);
        if ($key != null && $value != null) {
            $data = $data->where($key, 'LIKE', '%' . $value . '%');
        }
        return $data->simplePaginate(25);
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer',
            'course' => 'required|string|max:255',
            'board_university' => 'required|string|max:255',
            'year' => 'required|string|max:50',
            'result' => 'required|in:Pass,Fail',
            'aggregate' => 'required|string|max:50',
            'document' => 'required|file|mimes:pdf,png,jpg,jpeg|max:5120',
        ]);

        if ($file = $request->file('document')) {
            $name = time().'_'.mt_rand(100000, 999999).'_'.$file->getClientOriginalName();
            $file->storeAs('docs', $name, 'public');
            $path = "/docs/" . $name;

            $validated['document'] = $path;

            return EmployeeEducation::create($validated);
        }

        return response()->json(['error' => 'File upload failed'], 400);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:employee_educations,id',
            'employee_id' => 'required|integer',
            'course' => 'required|string|max:255',
            'board_university' => 'required|string|max:255',
            'year' => 'required|string|max:50',
            'result' => 'required|in:Pass,Fail',
            'aggregate' => 'required|string|max:50',
            'document' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:5120',
        ]);

        $education = EmployeeEducation::find($request->id);

        if ($file = $request->file('document')) {
            $name = time().'_'.mt_rand(100000, 999999).'_'.$file->getClientOriginalName();
            $file->storeAs('docs', $name, 'public');
            $path = "/docs/" . $name;

            // Delete old file
            if ($education->document) {
                $old_path = ltrim($education->document, '/');
                if (Storage::disk('public')->exists($old_path)) {
                    Storage::disk('public')->delete($old_path);
                }
            }

            $validated['document'] = $path;
        } else {
            // Keep the old file path if no new file is uploaded
            unset($validated['document']);
        }

        $education->update($validated);
        return $education;
    }

    public function delete(Request $request)
    {
        $education = EmployeeEducation::find($request->id);
            
        if ($education) {
            if ($education->document) {
                $old_path = ltrim($education->document, '/');
                if (Storage::disk('public')->exists($old_path)) {
                    Storage::disk('public')->delete($old_path);
                }
            }
            $education->delete();
        }

        return response()->json(['success' => true]);
    }
}

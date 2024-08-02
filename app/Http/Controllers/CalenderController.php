<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkingYear;
use App\Models\SpecialDays;
use App\Http\Requests\WorkingYearRequest;

class CalenderController extends Controller
{
    public function calender(){
        return view("calender");
    }

    public function working_years(){
        return WorkingYear::orderBy('yyyy', 'desc')->get(['id as val', 'yyyy as key']);
    }

    public function add(WorkingYearRequest $request){
        return WorkingYear::create($request->all());
    }

    public function fetch($year){
        return SpecialDays::whereYear('special_day', $year)->orderBy('special_day', 'asc')->get();
    }

    public function save(Request $request){
        foreach($request->dates as $dt){

            $data = [
                'special_day' => $dt,
                'remark' => $request->remark,
                'day_type' => $request->day_type
            ];

            SpecialDays::create($data);

        }
    }

    public function delete(Request $request){
        foreach($request->dates as $dt){
            SpecialDays::where('special_day', $dt)->delete();
        }
    }
}

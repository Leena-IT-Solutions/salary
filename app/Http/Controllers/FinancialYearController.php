<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinancialYear;

class FinancialYearController extends Controller
{
    public function financial_year(){
        return view("application_settings.financial_year");
    }

    public function fetch(Request $request){
        $by = 'id';
        $order = 'desc';
        $key = null;
        $value = null;

        $by = isset($request->by) ? $request->by : $by;
        $order = isset($request->order) ? $request->order : $order;
        $key = isset($request->key) ? $request->key : $key;
        $value = isset($request->value) ? $request->value : $value;

        $items = FinancialYear::orderBy($by, $order);
        if($key != null && $value != null){
            $items = $items->where($key, 'LIKE', '%'.$value.'%');
        }
        return $items->simplePaginate(25);
    }

    public function add(Request $request){
        if($request->is_current_year == 'Yes'){
            FinancialYear::where('is_current_year', 'Yes')->update([
                'is_current_year' => 'No'
            ]);
        }
        return FinancialYear::create($request->all());
    }

    public function update(Request $request){
        if($request->is_current_year == 'Yes'){
            FinancialYear::where('is_current_year', 'Yes')->update([
                'is_current_year' => 'No'
            ]);
        }
        return FinancialYear::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return FinancialYear::find($request->id)->delete();
    }
}

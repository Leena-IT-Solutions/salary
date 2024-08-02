<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveMaster;
use App\Models\LeaveGroup;
use App\Models\LeaveGroupHead;
use App\Http\Requests\LeaveGroupRequest;

class LeavesSetupController extends Controller
{
    public function leaves_setup(){
        $leaves = LeaveMaster::orderBy('leave_type', 'asc')->get();
        return view('settings.leaves_setup', ['leaves' => $leaves]);
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

        $designations = LeaveMaster::orderBy($by, $order);
        if($key != null && $value != null){
            $designations = $designations->where($key, 'LIKE', '%'.$value.'%');
        }
        return $designations->simplePaginate(25);
    }

    public function add(Request $request){
        return LeaveMaster::create($request->all());
    }

    public function update(Request $request){
        return LeaveMaster::find($request->id)->update($request->all());
    }

    public function delete(Request $request){
        return LeaveMaster::find($request->id)->delete();
    }

    public function fetch_lg(){
        return LeaveGroup::with('lgh.leave_master')->orderBy('id', 'desc')->get();
    }

    public function save_lg(LeaveGroupRequest $request){
        $lg = LeaveGroup::create([
            'name' => $request->name,
            'total_leaves' => $request->total_leaves
        ]);

        $leave_group_id = $lg->id;

        foreach($request->heads as $hd){
            if($hd['x'] > 0){
                LeaveGroupHead::create([
                    'leave_group_id' => $leave_group_id,
                    'leave_master_id' => $hd['val'],
                    'no_of_leaves' => $hd['x']
                ]);
            }
        }

        return $lg;
    }

    public function update_lg(LeaveGroupRequest $request){
        
        LeaveGroupHead::where('leave_group_id', $request->id)->delete();

        $lg = LeaveGroup::find($request->id);
        $lg->update([
            'name' => $request->name,
            'total_leaves' => $request->total_leaves
        ]);

        foreach($request->heads as $hd){
            if($hd['x'] > 0){
                LeaveGroupHead::create([
                    'leave_group_id' => $request->id,
                    'leave_master_id' => $hd['val'],
                    'no_of_leaves' => $hd['x']
                ]);
            }
        }

        return $lg;
    }

    public function delete_lg(Request $request){
        LeaveGroupHead::where('leave_group_id', $request->id)->delete();
        return LeaveGroup::find($request->id)->delete();
    }
}

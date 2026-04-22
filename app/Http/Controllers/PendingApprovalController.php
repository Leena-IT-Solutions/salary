<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinancialYear;
use App\Models\LeaveApproval;
use App\Models\TimeUpdate;
use App\Models\ShortLeave;
use App\Models\OvertimeApproval;
use Illuminate\Support\Facades\DB;

class PendingApprovalController extends Controller
{
    public function index()
    {
        $fy = FinancialYear::where('is_current_year', 'Yes')->first();
        return view("approvals.pending", compact('fy'));
    }

    public function fetch(Request $request)
    {
        $pendingRequests = [];
        $errors = [];

        try {
            // 1. Leave Requests
            $leaves = LeaveApproval::with(['employee', 'leave_master'])
                ->where('status', 'Pending')
                ->get()
                ->map(function ($item) {
                    if (!$item->employee) return null;
                    return [
                        'id' => $item->id,
                        'type' => 'Leave',
                        'employee' => $item->employee->first_name . ' ' . $item->employee->last_name,
                        'employee_code' => $item->employee->employee_code,
                        'date' => $item->on_date,
                        'details' => ($item->leave_master ? $item->leave_master->leave_type : 'Leave') . ($item->is_halfday == 'Yes' ? ' (Half Day)' : ''),
                        'reason' => $item->reason,
                        'status' => 'Pending',
                        'created_at' => $item->created_at ? $item->created_at->diffForHumans() : 'Recently',
                    ];
                })->filter();
            
            $leaveCount = $leaves->count();
            $pendingRequests = array_merge($pendingRequests, $leaves->toArray());
        } catch (\Exception $e) { 
            $leaveCount = 0;
            $errors[] = "Leave error: " . $e->getMessage(); 
        }

        try {
            // 2. Time Updates
            $timeUpdates = TimeUpdate::with('employee')
                ->where('status', 'Pending')
                ->get()
                ->map(function ($item) {
                    if (!$item->employee) return null;
                    return [
                        'id' => $item->id,
                        'type' => 'Time Update',
                        'employee' => $item->employee->first_name . ' ' . $item->employee->last_name,
                        'employee_code' => $item->employee->employee_code,
                        'date' => $item->on_date,
                        'details' => "Punch: {$item->in_time} - {$item->out_time}",
                        'reason' => $item->reason,
                        'status' => 'Pending',
                        'created_at' => $item->created_at ? $item->created_at->diffForHumans() : 'Recently',
                    ];
                })->filter();
            $timeCount = $timeUpdates->count();
            $pendingRequests = array_merge($pendingRequests, $timeUpdates->toArray());
        } catch (\Exception $e) { 
            $timeCount = 0;
            $errors[] = "TimeUpdate error: " . $e->getMessage(); 
        }

        try {
            // 3. Short Leaves
            $shortLeaves = ShortLeave::with('employee')
                ->where('status', 'Pending')
                ->get()
                ->map(function ($item) {
                    if (!$item->employee) return null;
                    return [
                        'id' => $item->id,
                        'type' => 'Short Leave',
                        'employee' => $item->employee->first_name . ' ' . $item->employee->last_name,
                        'employee_code' => $item->employee->employee_code,
                        'date' => $item->on_date,
                        'details' => "Time: {$item->in_time} - {$item->out_time}",
                        'reason' => $item->reason,
                        'status' => 'Pending',
                        'created_at' => $item->created_at ? $item->created_at->diffForHumans() : 'Recently',
                    ];
                })->filter();
            $shortCount = $shortLeaves->count();
            $pendingRequests = array_merge($pendingRequests, $shortLeaves->toArray());
        } catch (\Exception $e) { 
            $shortCount = 0;
            $errors[] = "ShortLeave error: " . $e->getMessage(); 
        }

        try {
            // 4. Overtime
            $overtime = OvertimeApproval::with('employee')
                ->where('status', 'Pending')
                ->get()
                ->map(function ($item) {
                    if (!$item->employee) return null;
                    return [
                        'id' => $item->id,
                        'type' => 'Overtime',
                        'employee' => $item->employee->first_name . ' ' . $item->employee->last_name,
                        'employee_code' => $item->employee->employee_code,
                        'date' => $item->on_date,
                        'details' => "Hours: {$item->hrs}",
                        'reason' => $item->note,
                        'status' => 'Pending',
                        'created_at' => $item->created_at ? $item->created_at->diffForHumans() : 'Recently',
                    ];
                })->filter();
            $otCount = $overtime->count();
            $pendingRequests = array_merge($pendingRequests, $overtime->toArray());
        } catch (\Exception $e) { 
            $otCount = 0;
            $errors[] = "Overtime error: " . $e->getMessage(); 
        }

        // Sort by id descending
        usort($pendingRequests, function($a, $b) {
            return $b['id'] <=> $a['id'];
        });

        return response()->json([
            'data' => $pendingRequests,
            'stats' => [
                'total' => count($pendingRequests),
                'leave' => $leaveCount,
                'time' => $timeCount,
                'short' => $shortCount,
                'overtime' => $otCount,
            ],
            'errors' => $errors
        ]);
    }

    public function updateStatus(Request $request)
    {
        $id = $request->id;
        $type = $request->type;
        $status = $request->status; // Approved or Rejected

        switch ($type) {
            case 'Leave':
                LeaveApproval::where('id', $id)->update(['status' => $status]);
                break;
            case 'Time Update':
                TimeUpdate::where('id', $id)->update(['status' => $status]);
                break;
            case 'Short Leave':
                ShortLeave::where('id', $id)->update(['status' => $status]);
                break;
            case 'Overtime':
                OvertimeApproval::where('id', $id)->update(['status' => $status]);
                break;
            default:
                return response()->json(['message' => 'Invalid type'], 422);
        }

        return response()->json(['message' => "Request {$status} successfully"]);
    }
}

<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use App\Models\EmployeeShift;
use App\Http\Controllers\AttendanceMachineController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use DateTime;
use DatePeriod;
use DateInterval;

class EvaluateAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $from;
    public $to;
    public $eids;
    public $jobId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $from, string $to, array $eids, string $jobId)
    {
        $this->from = $from;
        $this->to = $to;
        $this->eids = $eids;
        $this->jobId = $jobId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Uniqueness lock: prevent concurrent runs of the same evaluation
        $lockKey = 'attendance_evaluation_lock';
        $lock = Cache::lock($lockKey, 300); // 5 minutes lock

        if (!$lock->get()) {
            // Already running
            return;
        }

        try {
            $period = new DatePeriod(
                new DateTime($this->from),
                new DateInterval('P1D'),
                new DateTime(date('Y-m-d', strtotime('+1 Day', strtotime($this->to))))
            );

            $dates = [];
            foreach ($period as $value) {
                $dates[] = $value->format('Y-m-d');
            }

            $settings = new SettingsController();
            $totalEmployees = count($this->eids);
            $processedCount = 0;

            // Initialize progress
            Cache::put("attendance_job_{$this->jobId}", [
                'status' => 'processing',
                'processed' => 0,
                'total' => $totalEmployees,
            ], 600);

            foreach ($this->eids as $employee_id) {
                // Wrap each employee's range evaluation in a local transaction
                // (Though MyISAM won't roll back, InnoDB tables if any will, and it keeps it clean)
                DB::transaction(function () use ($employee_id, $dates, $settings) {
                    $shifts = EmployeeShift::where('employee_id', $employee_id)
                        ->whereBetween('dt', [$this->from, $this->to])
                        ->with(['working_shift', 'employee_attendance', 'special_days', 'leave', 'time_update', 'short_leave', 'on_duty'])
                        ->get()
                        ->keyBy('dt');

                    foreach ($dates as $dd) {
                        $employee_shift = $shifts->get($dd);
                        if ($employee_shift) {
                            $amc = new AttendanceMachineController();
                            $req = new Request();
                            $req->on_date = $dd;
                            $req->employee_id = $employee_id;
                            $req->employee_shift = $employee_shift;
                            $req->settings = $settings;
                            $amc->evalute($req);
                        }
                    }
                });

                $processedCount++;
                Cache::put("attendance_job_{$this->jobId}", [
                    'status' => 'processing',
                    'processed' => $processedCount,
                    'total' => $totalEmployees,
                ], 600);
            }

            // Mark complete
            Cache::put("attendance_job_{$this->jobId}", [
                'status' => 'completed',
                'processed' => $totalEmployees,
                'total' => $totalEmployees,
            ], 600);

        } catch (\Exception $e) {
            Cache::put("attendance_job_{$this->jobId}", [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'processed' => $processedCount,
                'total' => $totalEmployees,
            ], 600);
            throw $e;
        } finally {
            $lock->release();
        }
    }
}

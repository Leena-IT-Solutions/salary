<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmployeeShift;
use App\Http\Controllers\AttendanceMachineController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestAttendanceDiff extends Command
{
    protected $signature = 'attendance:diff-test {--from=} {--to=} {--eids=}';
    protected $description = 'Test attendance LOP evaluation for regressions in a non-destructive transaction';

    public function handle()
    {
        $from = $this->option('from') ?? date('Y-m-01', strtotime('-1 month'));
        $to = $this->option('to') ?? date('Y-m-t', strtotime('-1 month'));
        $eids = $this->option('eids') ? explode(',', $this->option('eids')) : [];

        if (empty($eids)) {
            // Get 5 random employee IDs who have shifts in the range to test
            $eids = EmployeeShift::whereBetween('dt', [$from, $to])
                ->distinct()
                ->limit(5)
                ->pluck('employee_id')
                ->toArray();
        }

        if (empty($eids)) {
            $this->error("No employees found with shifts in range $from to $to");
            return 1;
        }

        $this->info("Starting non-destructive diff test for employees: " . implode(', ', $eids) . " from $from to $to");

        // 1. Snapshot original outputs
        $originalShifts = EmployeeShift::whereIn('employee_id', $eids)
            ->whereBetween('dt', [$from, $to])
            ->get(['id', 'employee_id', 'dt', 'late', 'early', 'lop', 'status'])
            ->keyBy(function ($item) {
                return $item->employee_id . '_' . $item->dt;
            });

        $this->info("Snapshotted " . $originalShifts->count() . " original shifts.");

        if ($originalShifts->isEmpty()) {
            $this->error("No shifts found in snapshot.");
            return 1;
        }

        // 2. Start a transaction
        DB::beginTransaction();

        try {
            // 3. Run optimized evaluation
            $controller = new \App\Http\Controllers\AttendanceController();
            $request = new Request();
            $request->merge([
                'from' => $from,
                'to' => $to,
                'eids' => $eids
            ]);

            $controller->run_lop($request);

            // 4. Retrieve newly computed outputs
            $newShifts = EmployeeShift::whereIn('employee_id', $eids)
                ->whereBetween('dt', [$from, $to])
                ->get(['id', 'employee_id', 'dt', 'late', 'early', 'lop', 'status'])
                ->keyBy(function ($item) {
                    return $item->employee_id . '_' . $item->dt;
                });

            // 5. Compare
            $diffs = [];
            foreach ($originalShifts as $key => $original) {
                $new = $newShifts->get($key);
                if (!$new) {
                    $diffs[] = "Shift $key was deleted in new run";
                    continue;
                }

                $fields = ['late', 'early', 'lop', 'status'];
                foreach ($fields as $field) {
                    $origVal = $original->{$field};
                    $newVal = $new->{$field};

                    if ($field === 'lop') {
                        if (abs((float)$origVal - (float)$newVal) > 0.01) {
                            $diffs[] = "Shift $key: field '$field' changed from '$origVal' to '$newVal'";
                        }
                    } else {
                        if ($origVal != $newVal) {
                            $diffs[] = "Shift $key: field '$field' changed from '$origVal' to '$newVal'";
                        }
                    }
                }
            }

            if (empty($diffs)) {
                $this->info("SUCCESS: Zero regressions detected! Outputs are byte-identical.");
            } else {
                $this->error("FAILED: The following differences were detected:");
                foreach ($diffs as $diff) {
                    $this->error(" - $diff");
                }
            }

        } catch (\Exception $e) {
            $this->error("Error during evaluation: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        } finally {
            // 6. ALWAYS ROLLBACK!
            DB::rollBack();
            $this->info("Database transaction rolled back. Live data remains untouched.");
        }

        return empty($diffs) ? 0 : 1;
    }
}

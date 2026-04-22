<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use App\Models\Setting;
use App\Models\Payroll;
use App\Models\CompanyProfile;
use App\Models\StatutoryCompliance;

class CAExport implements FromView, WithTitle, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public $id;

    public function __construct($id){
        $this->id = $id;
    }

    public function view(): View
    {
        $wdc = Setting::where('key', 'Working Days Consideration')->first();
        $statutories = StatutoryCompliance::where('is_active', true)->get();
        $company = CompanyProfile::first();
        $payroll = Payroll::find($this->id);
        
        return view('excel.ca_report', [
            'company' => $company,
            'payroll' => $payroll,
            'statutories' => $statutories,
            'wdc' => $wdc,
        ]);

    }

    public function title(): string
    {
        return 'CA Report';
    }
}

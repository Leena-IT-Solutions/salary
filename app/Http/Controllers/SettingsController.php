<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Setting;

class SettingsController extends Controller
{
    public $cycle_day;
    public $late_minutes;
    public $late_days;
    public $late_penalty;
    public $late_hrmin;
    public $late_prorata;
    public $early_minutes;
    public $early_days;
    public $early_penalty;
    public $early_hrmin;
    public $early_prorata;
    public $workingdc;
    
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);

        $this->cycle_day        = Setting::where('key', 'Salary Cycle Start Date')->first()->value;
        $this->late_minutes     = Setting::where('key', 'Late Minutes')->first()->value;
        $this->late_days        = Setting::where('key', 'Late Days')->first()->value;
        $this->late_penalty     = Setting::where('key', 'Penalty On Late Mark in LOP')->first()->value;
        $this->late_hrmin       = Setting::where('key', 'On Late Calculate LOP as per')->first()->value;
        $this->late_prorata     = Setting::where('key', "Calculate Late Day's Salary on Pro-rata basis")->first()->value;
        $this->early_minutes    = Setting::where('key', 'Early Going Minutes')->first()->value;
        $this->early_days       = Setting::where('key', 'Early Going Days')->first()->value;
        $this->early_penalty    = Setting::where('key', 'Penalty On Early Going Mark in LOP')->first()->value;
        $this->early_hrmin      = Setting::where('key', 'On Early Going Calculate LOP as per')->first()->value;
        $this->early_prorata    = Setting::where('key', "Calculate Early Going Day's Salary on Pro-rata basis")->first()->value;
        $this->workingdc              = Setting::where('key', 'Working Days Consideration')->first()->value;
        
    }

    
}

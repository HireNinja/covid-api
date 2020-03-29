<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cases;
use App\Models\Location;
use App\Models\Tracker;

class TrackerCommand extends Command
{
    protected $signature ="tracker";
    protected $description = "A script that runs forever";
    protected $location; 

    public function handle()
    {
        $this->location = Location::OrderBy('last_tracked_at', 'asc')->first();
        $loc_uuids = Location::GetChildrenUuids($this->location->uuid);
        $this->trackDailyConfirmed($loc_uuids);
        $this->trackDailyDeaths($loc_uuids);
        $this->trackDailyRecovered($loc_uuids);
        Tracker::TrackTotal(date("Y-m-d"), $this->location->uuid);
        $this->fixTotalFromInterval();

        $this->location->last_tracked_at = \DB::raw('now()');
        $this->location->save();
    }

    public function fixTotalFromInterval() {
        $date = "2020-02-26";
        $end_date = date("Y-m-d"); 

        while (\strtotime($date) <= \strtotime($end_date)) {
            Tracker::TrackTotal($date, $this->location->uuid);
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
        }
    }


    public function trackDailyConfirmed($loc_uuids) {
        $cases = Cases::GetDailyConfirmed($loc_uuids);
        foreach ($cases as $case){
            Tracker::Track($case->toArray(), $this->location->uuid);
        }
    }

    public function trackDailyDeaths($loc_uuids) {
        $cases = Cases::GetDailyDeaths($loc_uuids);
        foreach ($cases as $case){
            Tracker::Track($case->toArray(), $this->location->uuid);
        }
    }

    public function trackDailyRecovered($loc_uuids) {
        $cases = Cases::GetDailyRecovered($loc_uuids);
        foreach ($cases as $case){
            Tracker::Track($case->toArray(), $this->location->uuid);
        }
    }

}

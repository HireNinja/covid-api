<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cases;
use App\Models\Location;
use App\Models\Tracker;

class RetroactiveTracker extends Command
{
    protected $signature ="retroactive";
    protected $description = "A script that runs forever";
    protected $location;

    public function handle()
    {
        $this->location = Location::OrderBy('last_tracked_at', 'asc')->first();
        $loc_uuids = Location::GetChildrenUuids($this->location->uuid);
        $this->fixTotalFromInterval();
    }

    public function fixTotalFromInterval()
    {
        $date = "2020-02-26";
        $end_date = date("Y-m-d");

        while (\strtotime($date) <= \strtotime($end_date)) {
            Tracker::TrackTotal($date, $this->location->uuid);
            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
        }
    }
}

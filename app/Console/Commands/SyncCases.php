<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cases;

class SyncCases extends Command
{
    protected $signature ="sync:cases {country}";
    protected $description = "A script that runs forever";

    public function handle()
    {
        $country = config('vars.countries.' . $this->argument('country'));
        if (!$country) {
            $this->error("Error: Please enter the correct country code e.g. PK, US");
            return;
        }

        $json = file_get_contents(__DIR__ . "/../../../resources/json/timeseries.json"); 
        $time_series = \json_decode($json, true);

        if (!isset($time_series[$country])) {
            $this->error("Error: No data found for " . $country);
            return;
        }
        $data = $time_series[$country];
        foreach ($data as $entry) {
            $this->info($entry["date"] . ": " . $entry['confirmed'] . ": " . $entry['recovered'] . ": " . $entry['deaths']);
        }
    }
}

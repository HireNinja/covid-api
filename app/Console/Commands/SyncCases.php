<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cases;
use App\Models\Location;

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
        $last = ['confirmed' => 0,'deaths' => 0,'recovered' => 0];
        $location = Location::GetLocationByAddress($country);

        foreach ($data as $entry) {
            $this->handleConfirmed($entry, $last, $location);
            $last = $entry;
        }
        $last = ['confirmed' => 0,'deaths' => 0,'recovered' => 0];
        foreach ($data as $entry) {
            $this->handleDeaths($entry, $last, $location);
            $last = $entry;
        }

        $last = ['confirmed' => 0,'deaths' => 0,'recovered' => 0];
        foreach ($data as $entry) {
            $this->handleRecovered($entry, $last, $location);
            $last = $entry;
        }
    }

    public function handleConfirmed($entry, $last, $location) {
        $new_cases = $entry['confirmed'] - $last['confirmed'];
       
        // New Cases per day
        if ($new_cases > 0) {
            Cases::Generate(
                $new_cases, 
                $location->uuid,
                [
                    'is_confirmed' => true, 
                    'confirmed_at' => $entry['date'],
                    'location_uuid' => $location->uuid
                ]
            );
            $this->info($entry['date'] . ' : ' . $new_cases);
        }
    }

    public function handleDeaths($entry, $last, $location) {
        $new_deaths = $entry['deaths'] - $last['deaths'];

        // New Deaths per day
        if ($new_deaths > 0) {
            Cases::UpdateDeaths(
                $new_deaths,
                $location->uuid,
                [
                    'is_dead' => true,
                    'died_at' => $entry['date'],
                    'location_uuid' => $location->uuid
                ],
                [
                    ['location_uuid',$location->uuid]
                ]
            );
        }
    }

    public function handleRecovered($entry, $last, $location) {
        $new_recovered = $entry['recovered'] - $last['recovered'];

        // New Deaths per day
        if ($new_recovered > 0) {
            Cases::UpdateRecovered(
                $new_recovered,
                $location->uuid,
                [
                    'is_recovered' => true,
                    'recovered_at' => $entry['date'],
                    'location_uuid' => $location->uuid
                ],
                [
                    ['location_uuid',$location->uuid]
                ]
            );
            $this->info("Recovered, $new_recovered");
        }
    }

    
}

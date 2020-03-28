<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cases;
use App\Services\Google;

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
        //$position = Google::GetPosition($country);

        foreach ($data as $entry) {
            $this->handleConfirmed($entry, $last, $country);
            $last = $entry;
        }
        $last = ['confirmed' => 0,'deaths' => 0,'recovered' => 0];
        foreach ($data as $entry) {
            $this->handleDeaths($entry, $last, $country);
            $last = $entry;
        }

        $last = ['confirmed' => 0,'deaths' => 0,'recovered' => 0];
        foreach ($data as $entry) {
            $this->handleRecovered($entry, $last, $country);
            $last = $entry;
        }
    }

    public function handleConfirmed($entry, $last, $country) {
        $new_cases = $entry['confirmed'] - $last['confirmed'];
       
        
        // New Cases per day
        if ($new_cases > 0) {
            Cases::Generate(
                $new_cases, 
                [
                    'is_confirmed' => true, 
                    'confirmed_at' => $entry['date'],
                    'country' => $country,
                    'position' => '(11,22)'
                ]
            );
            $this->info($entry['date'] . ' : ' . $new_cases);
        }
    }

    public function handleDeaths($entry, $last, $country) {
        $new_deaths = $entry['deaths'] - $last['deaths'];

        // New Deaths per day
        if ($new_deaths > 0) {
            Cases::UpdateDeaths(
                $new_deaths,
                [
                    'is_dead' => true,
                    'died_at' => $entry['date'],
                    'country' => $country
                ],
                [
                    ['country',$country]
                ]
            );
        }
    }

    public function handleRecovered($entry, $last, $country) {
        $new_recovered = $entry['recovered'] - $last['recovered'];

        // New Deaths per day
        if ($new_recovered > 0) {
            Cases::UpdateRecovered(
                $new_recovered,
                [
                    'is_recovered' => true,
                    'recovered_at' => $entry['date'],
                    'country' => $country
                ],
                [
                    ['country',$country]
                ]
            );
            $this->info("Recovered, $new_recovered");
        }
    }

    
}

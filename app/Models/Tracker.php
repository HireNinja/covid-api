<?php
namespace App\Models;

class Tracker extends Model
{
    protected $table = 'tracker';

    public static function Track($data, $location_uuid) {
        $track = self::getTrackerEntry($data['entry_date'], $location_uuid);
        $track->update($data);
    }

    public static function TrackTotal($entry_date, $location_uuid) {
        $track = self::getTrackerEntry($entry_date, $location_uuid);
        $data = Tracker::select(\DB::raw("sum(new_confirmed) as total_confirmed, sum(new_deaths) as total_deaths, sum(new_recovered) as total_recovered"))
            ->where("entry_date", "<=", $entry_date)->whereLocationUuid($location_uuid)
            ->first();
        
        $track->fill($data->toArray());
        $track->save();
    }

    private static function getTrackerEntry($entry_date, $location_uuid) {
        
        $track = Tracker::whereLocationUuid($location_uuid)->whereEntryDate($entry_date)->first();
        if ($track) {
            return $track;
        }
        return Tracker::create(['entry_date'=>$entry_date, 'location_uuid' => $location_uuid]);
    }

    public static function GetLocationStats($location_uuid) {
        $data = Tracker::whereLocationUuid($location_uuid)->orderBy('entry_date', 'desc')->limit(2);

        return $data;
    }
}

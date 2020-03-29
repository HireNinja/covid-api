<?php
namespace App\Models;

class Tracker extends Model
{
    protected $table = 'tracker';
    protected $hidden = ['deleted_at','created_at','updated_at','uuid'];

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

    public static function GetLocationStats($location) {
        $data = Tracker::whereLocationUuid($location->uuid)->orderBy('entry_date', 'desc')->limit(2)->get();
        if (count($data) < 2) {
            abort(404, "Sorry, we don't have enough data at a moment");
        }
        $today = $data[0];
        $yesterday = $data[1];

        $today['new_confirmed_growth'] = $yesterday['total_confirmed'] > 0 ? \ceil($today['new_confirmed'] / $yesterday['total_confirmed'] * 100) : '-' ; 
        $today['new_deaths_growth'] = $yesterday['total_deaths'] > 0 ? \ceil($today['new_deaths'] / $yesterday['total_deaths'] * 100) : '-' ;
        $today['new_recovered_growth'] = $yesterday['total_recovered'] > 0 ? \ceil($today['new_recovered'] / $yesterday['total_recovered'] * 100) : '-' ;

        $today['location'] = $location;

        return $today;
    }
}

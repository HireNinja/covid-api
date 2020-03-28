<?php
namespace App\Models;
use App\Services\Google;
use App\Helpers\DBArray;
class Location extends Model
{
    protected $table = 'locations';

    public function setPositionattribute($value) {
        $this->attributes['position'] = '(' . implode(',', $value) . ')';
    }
    public function setParentsAttribute($value) {
        $this->attributes['parents'] = DBArray::toString($value);
    }

    public static function GetLocationByAddress($address) {
        $place = Google::GetPlace($address);        
        $location = Location::wherePlaceId($place['place_id'])->first();
        $parent_uuid = "";
        $parent_uuids = [];

        if (!$location) {
            foreach ($place['parents'] as $parent) {
                $parent_location = Location::whereShortName($parent['long_name'])->first(); 
                if (!$parent_location) {
                    $parent_place = Google::getPlace($parent['long_name']);
                    unset($parent_place['parents']);
                    if ($parent_uuid) {
                        $parent_place['parent_uuid'] = $parent_uuid;
                    }
                    $parent_place['parents'] = $parent_uuids;
                    $parent_location = Location::create($parent_place);
                }
                $parents_uuid[] = $parent_location->uuid;
                $parent_uuid = $parent_location->uuid;
            }
            unset($place['parents']); 
            if ($parent_uuid) {
                $place['parent_uuid'] = $parent_uuid;
            }
            $place['parents'] = $parents_uuid;
            $location = Location::create($place);
        }
        return $location;
    }
}

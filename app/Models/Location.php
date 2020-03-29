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

    public static function GetLocationByPlaceId($place_id) {
        
    }

    private static function fetchLocation($place) {
        $parent_uuid = "";
        $parent_uuids = [];

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
        return Location::create($place);
    }

    public static function GetLocationByAddress($address) {
        $place = Google::GetPlace($address);
        $location = Location::wherePlaceId($place['place_id'])->first();
        if (!$location) {
            $location = self::fetchLocation($place);
        }
        return $location;
    }


    public static function GetChildren($loc_uuid) {
        $builder = new \Staudenmeir\LaravelCte\Query\Builder(app('db')->connection());
        $q = app('db')->table('locations')
                ->where(function($q) use ($loc_uuid) {
                        $q->where('uuid', '=', $loc_uuid);
                    })
                ->selectRaw('uuid, short_name, parent_uuid, parents, position, 1 as level, array[uuid::varchar] as path_info, updated_at, created_at')
                ->unionAll(
                    app('db')->table('locations as c')
                        ->selectRaw('c.uuid, c.short_name, c.parent_uuid, c.parents, c.position, p.level + 1, p.path_info||c.uuid::varchar, c.updated_at, c.created_at')
                        ->join('location_tree as p', 'p.uuid', '=', 'c.parent_uuid')
                );

        $results = $builder
                        ->from('location_tree')
                        ->withRecursiveExpression('location_tree', $q)
                        ->whereNotExists(function($q) {
                            $q
                            ->select(app('db')->raw(1))
                            ->from('location_tree as t')
                            ->whereRaw('t.uuid = location_tree.uuid')
                            ->whereRaw('t.level > location_tree.level');
                        })
                        ->orderBy('level', 'desc')
                        ->get();
        $results = $results->map(function($result) {
            return json_decode(json_encode($result), TRUE);
        });
        return $results;
    }

    public static function GetChildrenUuids($loc_uuid) {
        $children = self::GetChildren($loc_uuid);
        $uuids = [];
        foreach ($children as $child) {
            $uuids[] = $child['uuid'];
        }
        return $uuids;
    }

}

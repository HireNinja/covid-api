<?php
namespace App\Models;

use App\Services\Google;
use App\Helpers\DBArray;
class Cases extends Model
{
    protected $table = 'cases';

    public static function Generate($num, $location_uuid, $data = []) {
        $count = Cases::whereLocationUuid($location_uuid)->whereIsConfirmed(true)->whereConfirmedAt($data['confirmed_at'])->count();
        if ($num > $count) {
            $num = $num - $count;
        } elseif ($num == $count) {
            return;
        }
        for($i=0; $i<$num; $i++){
            self::create($data);
        }
    }

    public static function UpdateDeaths($num, $location_uuid, $data = [], $where ){
        $died_at = $data['died_at'];
        $count = Cases::whereLocationUuid($location_uuid)->whereIsDead(true)->whereDiedAt($data['died_at'])->count();

        if ($num > $count) {
            $num = $num - $count;
        }else if ($num == $count) {
            return;
        }

        $where = array_merge($where, [
            ['confirmed_at', '<=', date('Y-m-d', strtotime('-7 day', strtotime($died_at)))],
            ['is_confirmed', true],
            ['is_dead', false],
            ['is_recovered', false]
        ]);
        
        $cases = Cases::where($where)->limit($num)->get()->toArray();
        $uuids = array_column($cases, 'uuid');

        Cases::whereIn('uuid', $uuids)->update($data);
        return;
    }

    public static function GetDailyDeaths($location_ids) {
        return self::GetDailyCount("is_dead","died_at","new_deaths",$location_ids);
    }
    public static function GetDailyConfirmed($location_ids) {
        return self::GetDailyCount("is_confirmed","confirmed_at","new_confirmed",$location_ids);
    }
    public static function GetDailyRecovered($location_ids) {
        return self::GetDailyCount("is_recovered","recovered_at","new_recovered",$location_ids);
    }

    public static function GetDailyCount($field, $group_by, $name, $location_ids) {
        $data = Cases::where($field, true)->whereIn("location_uuid",$location_ids)->orderBy($group_by)->groupBy($group_by)
            ->select(\DB::raw("count(*) as $name"), \DB::raw("$group_by::date as entry_date"))->get();

        return $data;
    }

    public static function UpdateRecovered($num,$location_uuid, $data = [], $where ){
        $recovered_at = $data['recovered_at'];
        $count = Cases::whereLocationUuid($location_uuid)->whereIsRecovered(true)->whereRecoveredAt($recovered_at)->count();

        if ($num > $count) {
            $num = $num - $count;
        }else if ($num == $count) {
            return;
        }

        $where = array_merge($where, [
            ['confirmed_at', '<=', date('Y-m-d', strtotime('-7 day', strtotime($recovered_at)))],
            ['is_confirmed', true],
            ['is_dead', false],
            ['is_recovered', false]
        ]);
        
        $cases = Cases::where($where)->limit($num)->get()->toArray();
        $uuids = array_column($cases, 'uuid');

        Cases::whereIn('uuid', $uuids)->update($data);
        return;
    }
}

Cases::creating(function($case) {
    $case->sku = uniqid();
});

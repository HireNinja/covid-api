<?php
namespace App\Models;

use App\Services\Google;
use App\Helpers\DBArray;
class Cases extends Model
{
    protected $table = 'cases';

    public function setPositionattribute($value) {
        $this->attributes['position'] = '(' . implode(',', $value) . ')';
    }

    public static function Generate($num, $data = []) {
        $count = Cases::whereCountry($data['country'])->whereIsConfirmed(true)->whereConfirmedAt($data['confirmed_at'])->count();
        if ($num > $count) {
            $num = $num - $count;
        } elseif ($num == $count) {
            return;
        }
        $full_address = '';
        if (array_key_exists('address', $data)) {
            $full_address = $data['adress'];
        }
         if (array_key_exists('city', $data)) {
            $full_address .= $data['city'] . ', ';
        }
        $full_address .= $data['country'];
        $position = Google::GetPosition($full_address);        
        $data = array_merge($data,[
            'position' => $position
        ]);

        for($i=0; $i<$num; $i++){
            self::create($data);
        }
    }

    public static function UpdateDeaths($num, $data = [], $where ){
        $died_at = $data['died_at'];
        $count = Cases::where($where)->whereIsDead(true)->whereDiedAt($data['died_at'])->count();

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

    public static function UpdateRecovered($num, $data = [], $where ){
        $recovered_at = $data['recovered_at'];
        $count = Cases::where($where)->whereIsRecovered(true)->whereRecoveredAt($recovered_at)->count();

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
    $case->sku = $case->country . "-" . "NA" . "-" . uniqid();
});

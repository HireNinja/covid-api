<?php

namespace App\Services;

use App\Helpers\Curl;

class Google
{   
    static protected $endpoints = [
        'geocode' => 'https://maps.googleapis.com/maps/api/geocode/json'
    ];

    public static function GetPosition($address) {
        $url = self::$endpoints['geocode']; 
        $params = [
            'address' => $address
        ];
        $data = self::call("GET", $url, $params);
        if (!$data) {
            return $data;
        }        
        $data = \json_decode($data, true);
        if (array_key_exists('results', $data) && count($data['results'])> 0 ) {
            $data = array_pop($data['results']);
        }else {
            abort(500, "Couldn't get to the geocoding results");
        }
        if (array_key_exists('geometry', $data)) {
            $location =  $data['geometry']['location'];
            return [$location['lat'], $location['lng']];
        }
        return false;
    }

    private static function call($method, $url, $params=[], $body=[])
    {
        if (!env("GOOGLE_API_KEY")){ 
            abort(500, "No google api key is set");
        }
        $params['key'] = env("GOOGLE_API_KEY");
        $url .= '?' . http_build_query($params);

        switch ($method) {
            case "GET":
                return Curl::get($url, $params);
            break;
            case "POST":
                return Curl::post($url, $body);
        }
        return false;
    }
}

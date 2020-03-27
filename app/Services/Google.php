<?php

namespace App\Service;

use App\Helpers\Curl;

class Google
{   
    static protected $endpoints = [
        'geocode' => 'https://maps.googleapis.com/maps/api/geocode/json'
    ];

    public static function GetGeocode($address) {
        $url = self::$endpoints['geocode']; 
        $params = [
            'address' => $address
        ];
        return self::call("GET", $url, $params);
    }

    private static function call($method, $url, $params=[], $body=[])
    {
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

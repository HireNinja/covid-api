<?php

namespace App\Services;

use App\Helpers\Curl;

class Google
{   
    static protected $endpoints = [
        'geocode' => 'https://maps.googleapis.com/maps/api/geocode/json',
    ];
    static protected $supported_types = ['locality', 'administrative_area_level_1','country'];

    public static function GetPlace($address) {
        $data = self::GetPlaceFromAddress($address);
        $response = [
            'place_id' => $data['place_id'],
            'formatted_address' => $data['formatted_address'], 
            'position' => [$data['geometry']['location']['lat'],$data['geometry']['location']['lat']],
            'parents' => []
        ];

        foreach ($data['address_components'] as $address) {
            foreach ($address['types'] as $type){
                if ($data['types'] == $address['types']) {
                    $response['short_name'] = $address['short_name'];
                    $response['long_name'] = $address['long_name'];
                    $response['type'] = array_shift($address['types']);
                }else if (\in_array($type, self::$supported_types)){
                    array_unshift($response['parents'], $address);
                }
            }
        }
        return $response; 
    }

    public static function GetPlaceFromAddress($address) {
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
        return $data;
    }

    public static function GetPosition($address) {
        $data = self::GetPlaceFromAddress($address);
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

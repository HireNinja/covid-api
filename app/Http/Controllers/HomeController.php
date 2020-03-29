<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\Google;
use App\Models\Location;
use App\Models\Cases;
use App\Models\Tracker;

class HomeController extends Controller
{
    public function __construct() {
    }

    public function index(Request $request) {
        $address = 'Pakistan';
        if ($request->has('location')) {
            $address = $request->location;
        }

        $location = Location::GetLocationByAddress($address);
    

        

        return $location;
    }

}

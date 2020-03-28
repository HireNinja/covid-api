<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Google;
use App\Models\Cases; 
use App\Models\Location;

class TestingController extends Controller
{
    public function __construct()
    {
        
    }

    public function test(Request $request)
    {
        $location = Location::GetLocationByAddress("Islamabad, Pakistan");

        return $location;
    }
}

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
        $location = Cases::GetTotalConfirmed(['ce1f4505-057e-4cd5-9abe-1da3ed59b96b']);

        return $location;
    }
}

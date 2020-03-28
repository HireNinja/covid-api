<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\Google;
use App\Models\Cases;

class HomeController extends Controller
{
    public function __construct() {
    }

    public function index(Request $request) {
        $location = 'Pakistan';
        if ($request->has('location')) {
            $location = $request->location;
        }

        return $location;
    }

}

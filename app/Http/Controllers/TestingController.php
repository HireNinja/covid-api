<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Google;

class TestingController extends Controller
{
    public function __construct()
    {
    }

    public function test(Request $request)
    {
        $position = Google::GetPosition("Islamabad, Pakistan");
        
        return ['position' => $position];
    }
}

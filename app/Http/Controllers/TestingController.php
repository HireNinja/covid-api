<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Google;
use App\Models\Cases; 

class TestingController extends Controller
{
    public function __construct()
    {
    }

    public function test(Request $request)
    {
        $position = Google::GetPosition("Islamabad, Pakistan");
        
        Cases::Generate(5, [
            'country' => 'Pakistan',
            'city' => 'Islamabad',
            'source' => 'https://www.dawn.com',
            'sku' => "asdfasdf",
        ]);

        return ['position' => $position];
    }
}

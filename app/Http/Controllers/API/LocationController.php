<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\APIController;
use Illuminate\Http\Request;
use App\Models\Location;

class LocationController extends APIController
{
    public function __construct()
    {
        $this->model = new Location();
        return parent::__construct();
    }




}

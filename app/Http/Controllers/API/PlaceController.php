<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\APIController;
use Illuminate\Http\Request;
use App\Models\Place;

class PlaceController extends APIController
{
    public function __construct()
    {
        $this->model = new Place();
        return parent::__construct();
    }
}

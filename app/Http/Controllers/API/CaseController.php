<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\APIController;
use Illuminate\Http\Request;
use App\Models\Cases;

class CaseController extends APIController
{
    public function __construct() {
        $this->model = new Cases();
        return parent::__construct();
    }

}


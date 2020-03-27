<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\APIController;
use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends APIController
{
    public function __construct()
    {
        $this->model = new Post();
        return parent::__construct();
    }
}

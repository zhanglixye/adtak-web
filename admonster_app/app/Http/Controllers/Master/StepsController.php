<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StepsController extends Controller
{
    public function index()
    {
        return view('master.steps.index');
    }
}

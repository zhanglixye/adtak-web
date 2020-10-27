<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;

class ReportsController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }
}

<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;

class BusinessesController extends Controller
{
    public function index(Request $req)
    {
        return view('businesses.index');
    }

    public function show(Request $req)
    {
        return view('businesses.show')->with([
            'business_id' => $req->business_id
        ]);
    }
}

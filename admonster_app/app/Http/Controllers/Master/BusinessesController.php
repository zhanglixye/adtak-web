<?php
/**
 * Created by PhpStorm.
 * User: yonghua-you
 * Date: 2020/07/01
 * Time: 19:20
 */

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BusinessesController extends Controller
{
    public function index()
    {
        return view('master.businesses.index');
    }

    public function show(Request $req)
    {
        return view('master.businesses.show')->with([
            'business_id' => $req->business_id
        ]);
    }
}

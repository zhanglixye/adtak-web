<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\RequestWork;
use Illuminate\Http\Request;

class RequestsController extends Controller
{
    public function show(Request $req)
    {
        $request_id = (int)$req->request_id;
        $request_work = RequestWork::where('request_id', $request_id)->orderBy('created_at')->first();

        return view('client.requests.show')->with([
            'request_id' => $request_id,
            'request_work_id' => $request_work->id,
        ]);
    }
}

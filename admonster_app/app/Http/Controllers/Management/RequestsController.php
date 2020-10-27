<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;

use App\Models\RequestWork;
use Illuminate\Http\Request;

class RequestsController extends Controller
{
    public function index(Request $req)
    {
        // GETパラメータで渡された検索条件を設定
        $form = null;
        if ($req->input()) {
            $form = [
                'business_name' => $req->input('business_name'),
                'request_file_id' => $req->input('request_file_id'),
                'date_type' => $req->input('date_type'),
                'from' => $req->input('from') ? $req->input('from') : '',
                'to' => $req->input('to') ? $req->input('to') : '',
                'status' => (int)$req->input('status', \Config::get('const.REQUEST_STATUS.ALL')),
            ];
        }

        return view('requests.index')->with([
            'inputs' => json_encode($form),
        ]);
    }

    public function show(Request $req)
    {
        $request_id = (int)$req->request_id;
        $request_work = RequestWork::where('request_id', $request_id)->orderBy('created_at')->first();

        return view('requests.show')->with([
            'request_id' => $request_id,
            'request_work_id' => $request_work->id,
            'reference_mode' => $req->input('reference_mode', 'false'),
        ]);
    }
}

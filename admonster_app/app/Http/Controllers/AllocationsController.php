<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DownloadFileManager\Downloader;

class AllocationsController extends Controller
{
    public function index(Request $req)
    {
        // GETパラメータで渡された検索条件を設定
        $form = null;
        if ($req->input()) {
            $from = $req->input('from') ? $req->input('from') : '';
            if (is_numeric($from)) {
                $from = (int)$from;
            }

            $to = $req->input('to') ? $req->input('to') : '';
            if (is_numeric($to)) {
                $to = (int)$to;
            }

            $date_type = $req->input('date_type');
            if (is_numeric($date_type)) {
                $date_type = (int)$date_type;
            }

            $form = [
                'request_work_ids' => $req->input('request_work_ids') ? $req->input('request_work_ids') : [],
                'business_name' => $req->input('business_name'),
                'step_name' => $req->input('step_name'),
                'date_type' => $date_type,
                'from' => $from,
                'to' => $to,
                'status' => $req->input('status') ? $req->input('status') : [],
            ];
        }

        return view('allocations.index')->with([
            'inputs' => json_encode($form),
        ]);
    }

    public function edit(Request $req, $request_work_id)
    {
        return view('allocations.single')->with([
            'inputs' => json_encode([
                'request_work_id' => $request_work_id,
                'reference_mode' => $req->input('reference_mode', 'false'),
            ]),
        ]);
    }
}

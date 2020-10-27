<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WorksController extends Controller
{
    public function index(Request $req)
    {
        // GETパラメータで渡された検索条件を設定
        $form = null;
        if ($req->input()) {
            $form = [
                'worker' => $req->input('worker'),
                'request_work_ids' => $req->input('request_work_ids') ? $req->input('request_work_ids') : [],
                'business_name' => $req->input('business_name'),
                'step_name' => $req->input('step_name'),
                'date_type' => $req->input('date_type'),
                'from' => $req->input('from') ? $req->input('from') : '',
                'to' => $req->input('to') ? $req->input('to') : '',
                'status' => $req->input('status') ? $req->input('status') : [],
                'approval_status' => $req->input('approval_status') ? $req->input('approval_status') : [],
                'task_contact' => $req->input('task_contact'),
            ];
        }

        return view('works.index')->with([
            'inputs' => json_encode($form),
        ]);
    }
}

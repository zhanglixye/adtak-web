<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApprovalsController extends Controller
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
                'approval_status' => $req->input('status') ? $req->input('status') : []
            ];
        }

        return view('approvals.index')->with([
            'inputs' => json_encode($form),
        ]);
    }

    public function edit(Request $req, $request_work_id)
    {
        return view('approvals.edit')->with([
            'inputs' => json_encode([
                'request_work_id' => $request_work_id,
                'reference_mode' => $req->input('reference_mode', 'false'),
            ]),
        ]);
    }

    public function filePreview(Request $req)
    {
        return view('approvals.filePreview')->with([
            'inputs' => json_encode([
                'file_preview' => $req->input('previewComparisonContent'),
                'local_storage_key' => $req->input('localStorageKey'),
                'step_id' => $req->input('stepId'),
                'is_wide' => $req->input('isWide'),
            ]),
        ]);
    }
}

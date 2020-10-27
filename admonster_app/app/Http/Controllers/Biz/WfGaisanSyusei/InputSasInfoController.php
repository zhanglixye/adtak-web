<?php

namespace App\Http\Controllers\Biz\WfGaisanSyusei;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InputSasInfoController extends WfGaisanSyuseiController
{
    // SAS情報入力画面
    public function create(Request $req)
    {
        return view('biz.wf_gaisan_syusei.input_sas_info.create')->with([
            'request_work_id' => $req->request_work_id,
            'task_id' => $req->task_id,
        ]);
    }
}

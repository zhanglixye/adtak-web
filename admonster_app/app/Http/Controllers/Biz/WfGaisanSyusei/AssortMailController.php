<?php

namespace App\Http\Controllers\Biz\WfGaisanSyusei;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AssortMailController extends WfGaisanSyuseiController
{
    // メール仕訳画面
    public function create(Request $req)
    {
        return view('biz.wf_gaisan_syusei.assort_mail.create')->with([
            'request_work_id' => $req->request_work_id,
            'task_id' => $req->task_id,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Biz\WfGaisanSyusei;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FinalJudgeController extends WfGaisanSyuseiController
{
    // 最終判定画面
    public function create(Request $req)
    {
        return view('biz.wf_gaisan_syusei.final_judge.create')->with([
            'request_work_id' => $req->request_work_id,
            'task_id' => $req->task_id,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Biz\Development;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\DownloadFileManager\Downloader;

class ImprovementController extends Controller
{
    // 経費承認画面
    public function create(Request $req)
    {
        return view('biz.development.improvement.create')->with([
            'request_work_id' => $req->request_work_id,
            'task_id' => $req->task_id,
            'reference_mode' => $req->input('reference_mode', 'false'),
        ]);
    }
}

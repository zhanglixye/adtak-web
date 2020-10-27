<?php

namespace App\Http\Controllers\Biz\WfGaisanSyusei;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\DownloadFileManager\Downloader;

class IdentifyPptController extends WfGaisanSyuseiController
{
    // パワポ特定画面
    public function create(Request $req)
    {
        return view('biz.wf_gaisan_syusei.identify_ppt.create')->with([
            'request_work_id' => $req->request_work_id,
            'task_id' => $req->task_id,
        ]);
    }

    public function downloadAttachmentFile(Request $req)
    {
        Downloader::downloadFromS3($req->attachment_file_path, $req->attachment_file_name);
    }
}

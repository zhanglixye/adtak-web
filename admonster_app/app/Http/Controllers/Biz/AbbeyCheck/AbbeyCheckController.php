<?php

namespace App\Http\Controllers\Biz\AbbeyCheck;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\DownloadFileManager\Downloader;

class AbbeyCheckController extends Controller
{
    // Abbeyチェック画面
    public function create(Request $req)
    {
        return view('biz.abbey_check.abbey_check.create')->with([
            'request_work_id' => $req->request_work_id,
            'task_id' => $req->task_id,
            'reference_mode' => $req->input('reference_mode', 'false'),
        ]);
    }
    public function downloadAttachmentFile(Request $req)
    {
        Downloader::downloadFromS3($req->attachment_file_path, $req->attachment_file_name);
    }
}

<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DownloadFileManager\Downloader;

class UtilitiesController extends Controller
{
    public function downloadFile(Request $req)
    {
        Downloader::downloadFromS3($req->file_path, $req->file_name);
    }
}

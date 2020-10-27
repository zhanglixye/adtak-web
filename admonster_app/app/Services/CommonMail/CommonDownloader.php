<?php

namespace App\Services\CommonMail;

use App\Models\RequestAdditionalInfoAttachment;
use App\Models\RequestMailAttachment;
use Carbon\Carbon;
use Storage;
use ZipArchive;

class CommonDownloader
{
    public function getDefaultPathPrefix()
    {
        //
    }

    /**
     * 从s3下载文件
     * @param string $file_path ファイルパス
     * @param string|null $file_name ファイル名
     * @throws \Exception
     */
    public static function downloadFromS3(string $file_path, string $file_name = null)
    {
        list($file_name, $tmp_disk, $tmp_file_name, $tmp_file_path, $mime_type, $file_size) = self::getFileFromS3($file_path, $file_name);

        mb_http_output("pass");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=" . $file_name);
        header("Content-Type: " . "application/octet-stream");
        // header("Content-Type: " . $mime_type);

        // ファイルの内容を出力する前に入力バッファの中身をクリアする
        ob_end_clean();

        // ダウンロード
        readfile($tmp_file_path);

        // 一時ファイルを削除
        $tmp_disk->delete($tmp_file_name);

        exit;
    }

    /**
     * s3からのファイルのBase64表現.
     * @param string $file_path ファイルパス
     * @param string|null $file_name ファイル名
     * @return array 文件的Base64表示
     * @throws \Exception
     */
    public static function base64FileFromS3(string $file_path, string $file_name = null): array
    {
        list($file_name, $tmp_disk, $tmp_file_name, $tmp_file_path, $mime_type, $file_size) = self::getFileFromS3($file_path, $file_name);

        $data = base64_encode(file_get_contents($tmp_file_path));
        $src = 'data:' . $mime_type . ';base64,' . $data;

        // 一時ファイルを削除
        $tmp_disk->delete($tmp_file_name);

        return array($src, $mime_type, $file_size ,$data);
    }

    /**
     * 从S3获取文件
     * @param string $file_path ファイルパス
     * @param string|null $file_name ファイル名
     * @param bool $mask_file_name ファイル名MD5
     * @return array file info
     * @throws \Exception
     */
    public static function getFileFromS3(string $file_path, string $file_name = null, bool $mask_file_name = false): array
    {
        $disk = \Storage::disk(\Config::get('filesystems.cloud'));

        // ファイルのURLを取得
        $url = $disk->url($file_path);

        // 指定ファイルが存在するか確認
        if (!$disk->exists($file_path)) {
            throw new \Exception('Download is failed. File not exists');
        }

        $file_name =  isset($file_name) ? $file_name : basename($url);
        $file_name = urldecode($file_name);

        // ローカルに一時保存
        $tmp_disk = Storage::disk('public');
        if ($mask_file_name) {
            $tmp_file_name = Carbon::now()->format('YmdHis') . Carbon::now()->micro . "_" . md5($file_name);
        } else {
            $tmp_file_name = Carbon::now()->format('YmdHis') . Carbon::now()->micro . "_" . $file_name;
        }
        $tmp_disk->put($tmp_file_name, $disk->get($file_path));
        $tmp_file_path = storage_path() . '/app/public/'. $tmp_file_name;
        $mime_type = \File::mimeType($tmp_file_path);
        $file_size = filesize($tmp_file_path);
        return array($file_name, $tmp_disk, $tmp_file_name, $tmp_file_path, $mime_type, $file_size);
    }


    public static function downloadFileToZipFromS3($req)
    {
        $file_ids = $req->input('file_ids') ? $req->input('file_ids') : [];
        $specified_file_name = $req->input('file_name') ? $req->input('file_name') : 'no_name';//ファイル名が空の状態を防ぐ

        if (count($file_ids) == 0) {
            throw new \Exception('No files to download.');
        }

        $disk = \Storage::disk(\Config::get('filesystems.cloud'));

        // TODO : 添付ファイル用のテーブルは複数存在するため、ここでは分岐しないで済むように汎用的な作りにする。
        // ※以下は応急処置
        $files = [];
        if ($req->input('file_table') &&  $req->input('file_table') == 'request_additional_info_attachments') {
            $files = RequestAdditionalInfoAttachment::whereIn('id', $file_ids)->get();
        } else {
            // ファイル情報を取得
            $files = RequestMailAttachment::whereIn('id', $file_ids)->get();
        }

        // 取得するファイルの長さが0の時の挙動はどうする？
        if (count($file_ids) != count($files)) {
            throw new \Exception('The number of files does not match.');
        }

        $managed_tmp_files = [];
        $tmp_local_file_path = null;

        try {
            // 対象のファイルをサーバに一時保存
            foreach ($files as $file) {
                // ファイルのURLを取得
                $file_path = $file->file_path;

                // 指定ファイルが存在するか確認
                if (!$disk->exists($file_path)) {
                    throw new \Exception('Download is failed. File not exists.');
                }

                // ローカルに一時保存
                $file_name =   $file->name;
                $tmp_disk = Storage::disk('public');
                $tmp_file_name = str_replace(".", "-", strval(microtime(true))). '-' . $file_name;
                $local_file_path = 'tmp/' . $tmp_file_name;
                $tmp_disk->put($local_file_path, $disk->get($file_path));
                $tmp_file_path = storage_path() . '/app/public/'. $local_file_path;
                $val = [
                    'file_name' => $file_name,
                    'file_full_path' => $tmp_file_path,
                    'local_disk_path' => $local_file_path
                ];
                array_push($managed_tmp_files, $val);
            }

            // zipファイルの作成
            $zip = new ZipArchive();
            // ZIPファイルをオープン
            $tmp_file_name = 'tmp.zip';
            $tmp_zip_file_name = str_replace(".", "-", strval(microtime(true))). '-' . $tmp_file_name;
            $tmp_local_file_path = 'tmp/' . $tmp_zip_file_name;
            $tmp_zip_file_path = storage_path() . '/app/public/' . $tmp_local_file_path;

            $res = $zip->open($tmp_zip_file_path, ZipArchive::CREATE);
            if ($res === true) {
                foreach ($managed_tmp_files as $file) {
                    // 圧縮するファイルを指定
                    $zip->addFile($file['file_full_path'], $file['file_name']);
                }
                $zip->close();
            } else {
                throw new \Exception("Failed to open zip file");
            }

            // 送信情報のセット
            mb_http_output("pass");
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            $zip_file_name = $specified_file_name . '.zip';
            header("Content-Disposition: attachment; filename=" . $zip_file_name);
            header("Content-Type: " . "application/octet-stream");

            // ファイルの内容を出力する前に入力バッファの中身をクリアする
            ob_end_clean();

            // ダウンロード
            readfile($tmp_zip_file_path);

            // 一時ファイルを削除
            $local_disk = Storage::disk('public');
            $local_disk->delete($tmp_local_file_path);//zip
            $local_disk->delete(array_column($managed_tmp_files, 'local_disk_path'));// 内包ファイル

            exit;
        } catch (\Throwable $th) {
            // 一時ファイルを削除
            $local_disk = Storage::disk('public');
            $local_disk->delete($tmp_local_file_path);// zip
            $local_disk->delete(array_column($managed_tmp_files, 'local_disk_path'));// 内包ファイル

            throw $th;
        }
    }
}

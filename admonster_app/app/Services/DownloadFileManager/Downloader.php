<?php

namespace App\Services\DownloadFileManager;

use Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Exception;

class Downloader
{
    public function getDefaultPathPrefix()
    {
        //
    }

    public static function downloadFromS3($file_path, $file_name = null)
    {
        $disk = \Storage::disk(\Config::get('filesystems.cloud'));

        // ファイルのURLを取得
        $url = $disk->url($file_path);

        // 指定ファイルが存在するか確認
        if (!$disk->exists($file_path)) {
            throw new \Exception('Download is failed. File not exists');
        }

        $file_name =  isset($file_name) ? $file_name : basename($url);

        // ローカルに一時保存
        $tmp_disk = Storage::disk('public');
        $tmp_file_name = time() . $file_name;
        $tmp_disk->put($tmp_file_name, $disk->get($file_path));
        $tmp_file_path = storage_path() . '/app/public/'. $tmp_file_name;
        $mime_type = \File::mimeType($tmp_file_path);

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
     * ローカルからファイルをダウンロードする
     *
     * @param string $file_path
     * @param string $file_name
     * @return BinaryFileResponse
     * @throws Exception
     */
    public static function downloadFromLocal($file_path, $file_name): BinaryFileResponse
    {

        $local_disk = \Storage::disk('public');
        try {
            // 指定ファイルが存在するか確認
            if (!$local_disk->exists($file_path)) {
                throw new \Exception('Download is failed. File not exists');
            }

            // ファイル名を取得
            $file_name = $file_name !== '' ? $file_name : basename($file_path);

            // download
            $header = [
                'X-Content-Type-Options' => 'nosniff',//MIME スニッフィングを抑制
            ];
            return response()->download($local_disk->path($file_path), $file_name, $header)
                ->deleteFileAfterSend(true);//送信したファイルを削除する
        } catch (\Exception $e) {
            $local_disk->delete($file_path);//一時ファイルを削除する
            throw $e;// ファイルのDLが行えているように見える問題を回避する
        }
    }

    /**
     * @param string $file_path
     * @return string
     * @throws Exception
     */
    public static function getFileContentFromS3(string $file_path): string
    {
        $disk = \Storage::disk(\Config::get('filesystems.cloud'));

        // 指定ファイルが存在するか確認
        if (!$disk->exists($file_path)) {
            throw new \Exception('Download is failed. File not exists');
        }
        return $disk->get($file_path);
    }
}

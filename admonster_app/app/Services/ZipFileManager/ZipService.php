<?php

namespace App\Services\ZipFileManager;

use ZipArchive;

class ZipService
{
    /**
     * @param array{array{file_name:string,local_path:string}} $local_files $local_files[local_path]にはpublicより下のパスを入れる。
     * @param string $file_name
     * @param string $directory_path Path Exists. Example "pre_folder/child_folder"
     * @return array{file_name:string,local_path:string,full_path:string}
     */
    public static function compress(array $local_files, string $file_name = 'tmp.zip', string $directory_path = ''): array
    {
        // zipファイルの作成
        $local_disk = \Storage::disk('public');
        $remove_zip_local_path = '';
        $is_open_zip_archive = false;
        $zip = new ZipArchive();
        try {
            $tmp_zip_file_name = str_replace('.', '-', strval(microtime(true))) . '-' . $file_name;

            // 指定したフォルダが無い場合は作成
            if ($directory_path !== '' && !$local_disk->exists($directory_path)) {
                $local_disk->makeDirectory($directory_path);
            }

            $tmp_local_path = "{$directory_path}/{$tmp_zip_file_name}";
            $remove_zip_local_path = $tmp_local_path;
            // TODO publicフォルダにzipフォルダを生成してよいか再検討する
            $tmp_zip_full_path =  $local_disk->path($tmp_local_path);

            // ZIPファイルをオープン
            $res = $zip->open($tmp_zip_full_path, ZipArchive::CREATE);
            if ($res === true) {
                $is_open_zip_archive = true;
                foreach ($local_files as $file) {
                    // 圧縮するファイルを指定
                    $zip->addFile($local_disk->path($file['local_path']), $file['file_name']);
                }
                $zip->close();
                $is_open_zip_archive = false;
            } else {
                throw new \Exception("Failed to open zip file code:" . $res);
            }

            $return_associative_array = [
                'file_name' => $tmp_zip_file_name,
                'local_path' => $tmp_local_path,
                'full_path' => $tmp_zip_full_path
            ];
            return $return_associative_array;
        } catch (\Exception $e) {
            // Delete zip file
            // PHPStanのバグ（v0.12で改修される見込み https://github.com/phpstan/phpstan/issues/2082
            // larastan Error: Strict comparison using === between false and true will always evaluate to false.
            // if ($is_open_zip_archive === true) {
            if ($is_open_zip_archive == true) {
                $zip->close();// Prevent zip files from being created after catch process
            }
            $local_disk->delete($remove_zip_local_path);

            throw $e;
        }
    }
}

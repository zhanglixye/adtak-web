<?php

namespace App\Services\UploadFileManager;

use Storage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Services\FileManager\FileManagerCreater;
use App\Services\FileManager\AbstractFileManager;

class Uploader
{
    public function getDefaultPathPrefix()
    {
        //
    }

    public static function uploadToS3($file_contents, $file_path)
    {
        $disk = \Storage::disk(\Config::get('filesystems.cloud'));

        // 同じパスに同名ファイルが存在する場合エラー
        if ($disk->exists($file_path)) {
            throw new \Exception('File is already exists');
        }
        $disk->put($file_path, $file_contents, '');

        // ファイルのURLを取得
        $url = $disk->url($file_path);

        // 指定ファイルが存在するか確認
        if (!$disk->exists($file_path)) {
            throw new \Exception('Upload is failed');
        }
        return $url;
    }

    public static function deleteFromS3($file_path)
    {
        $disk = \Storage::disk(\Config::get('filesystems.cloud'));

        if (!$disk->exists($file_path)) {
            throw new \Exception('File is not exists');
        }
        $delete_flg = $disk->delete($file_path);
        return $delete_flg;
    }

    /**
     * ファイルのアップロードと指定されたテーブルに対して保存を行う。
     * $table_dataのデータを優先して使用する。
     * @param mixed $file_content file content
     * @param string $upload_path upload file path
     * @param string $model_class full model path Model::class
     * @param array $table_data table data
     * @return Model $model subclass of Illuminate\Database\Eloquent\Model
     * @throws \Throwable
     */
    public static function tryUploadAndSave($file_content, string $upload_path, string $model_class, array $table_data = []): Model
    {

        if (!is_subclass_of($model_class, Model::class)) {
            throw new \Exception($model_class . ' is not subclass of ' . Model::class);
        }

        $upload_disk = \Storage::disk(\Config::get('filesystems.cloud'));
        if ($upload_disk->exists($upload_path)) {
            throw new \Exception('File is already exists');
        }

        $disk_name = 'local';
        $disk = \Storage::disk($disk_name);
        $file_path = explode('/', $upload_path);

        // path for temporary file
        $microtime_float = explode('.', (microtime(true)).'.');
        $tmp_path = $microtime_float[0]. $microtime_float[1] . end($file_path);

        \DB::beginTransaction();
        try {
            // create temporary file to public
            $disk->put($tmp_path, $file_content);

            $user = \Auth::user();
            
            // template array
            $insert_data_table = [
                'name' => end($file_path),
                'file_path' => $upload_path,
                'size' => $disk->exists($tmp_path) ? $disk->size($tmp_path) : null,
                'created_at' => Carbon::now(),
                'created_user_id' => $user->id,
                'updated_at' => Carbon::now(),
                'updated_user_id' => $user->id
            ];

            /**
             * @var AbstractFileManager|null
             */
            $fm = FileManagerCreater::createFileManagerOrNull($tmp_path, $disk_name);
            if (!is_null($fm)) {
                $insert_data_table = array_merge(
                    $insert_data_table,
                    [
                        'width' => $fm->width(),
                        'height' => $fm->height()
                    ]
                );
            }

            $insert_data_table = array_merge($insert_data_table, $table_data);

            // テーブルへの保存
            /**
             * @var Model
             */
            $model = new $model_class;
            $model->fill($insert_data_table);// idがないテーブルがあり、insertGetIdが使えないので、fill
            $model->save();

            // ファイルのアップロード
            self::uploadToS3($file_content, $upload_path);

            \DB::commit();
            return $model;
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        } finally {
            if ($disk->exists($tmp_path)) {
                $disk->delete($tmp_path);
            }
        }
    }
}

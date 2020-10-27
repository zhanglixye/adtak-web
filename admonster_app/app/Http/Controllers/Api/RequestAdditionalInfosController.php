<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RequestLog;
use App\Models\User;
use Carbon\Carbon;
use App\Models\RequestAdditionalInfo;
use App\Models\RequestAdditionalInfoAttachment;
use App\Services\Traits\RequestLogStoreTrait;
use App\Services\UploadFileManager\Uploader;

class RequestAdditionalInfosController extends Controller
{
    use RequestLogStoreTrait;

    public function index(Request $req)
    {
        $user = \Auth::user();

        $request_additional_infos = RequestAdditionalInfo::getList($req)->with(['requestAdditionalInfoAttachments' => function ($query) {
            $query->where('is_deleted', 0);
        }])->get();

        return response()->json([
            'request_additional_infos' => $request_additional_infos,
        ]);
    }

    public function store(Request $req)
    {
        $user = \Auth::user();

        // DB登録
        \DB::beginTransaction();
        try {
            $request_additional_info = new RequestAdditionalInfo;
            $request_additional_info->request_id = $req->request_id;
            $request_additional_info->request_work_id = isset($req->request_work_id) ? $req->request_work_id : null;
            $request_additional_info->content = $req->content;
            $request_additional_info->is_open_to_client = $req->is_open_to_client;
            $request_additional_info->is_open_to_work = $req->is_open_to_work;
            $request_additional_info->created_at = Carbon::now();
            $request_additional_info->created_user_id = $user->id;
            $request_additional_info->updated_at = Carbon::now();
            $request_additional_info->updated_user_id = $user->id;
            $request_additional_info->save();

            // TODO : 対象依頼作業の指定がある場合はrequest_work_additional_infosにも登録
            // if ($req->target_request_work_ids) {

            // }

            // ファイル保存
            $file_list = array_values(array_filter($req->request_additional_info_attachments));

            foreach ($file_list as &$file) {
                $root_folder = substr($file['file_path'], 0, strlen('request_additional_info_attachments'));

                // アップロードされていないものだけを保存
                if ($file['file_path'] == null || $root_folder !== 'request_additional_info_attachments') {
                    // ファイルのアップロード
                    $upload_data = array(
                        'request_additional_info_id' => $request_additional_info->id,
                        'user_id' => $user->id,
                        'file' => $file
                    );
                    $request_additional_info_attachment = $this->uploadFile($upload_data, 'base64');
                }
            }

            // ログ登録
            $request_log_attributes = [
                'request_id' => $req->request_id,
                'request_work_id' => isset($req->request_work_id) ? $req->request_work_id : null,
                'type' => \Config::get('const.REQUEST_LOG_TYPE.ADDITIONAL_INFO_CREATED'),
                'created_user_id' => $user->id,
                'updated_user_id' => $user->id,
            ];
            $this->storeRequestLog($request_log_attributes);

            \DB::commit();

            return response()->json([
                'result' => 'success',
                'request_additional_info_id' => $request_additional_info->id,
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
            ]);
        }
    }

    public function update(Request $req)
    {
        $user = \Auth::user();

        $request_additional_info_id = $req->request_additional_info_id;
        // DB登録
        \DB::beginTransaction();
        try {
            $request_additional_info = RequestAdditionalInfo::find($request_additional_info_id);
            $request_additional_info->content = $req->content;
            $request_additional_info->is_open_to_client = $req->is_open_to_client;
            $request_additional_info->is_open_to_work = $req->is_open_to_work;
            $request_additional_info->updated_at = Carbon::now();
            $request_additional_info->updated_user_id = $user->id;
            $request_additional_info->save();

            // ファイル保存
            $file_list = array_values(array_filter($req->request_additional_info_attachments));

            foreach ($file_list as &$file) {
                $root_folder = substr($file['file_path'], 0, strlen('request_additional_info_attachments'));

                // アップロードされていないものだけを保存
                if ($file['file_path'] == null || $root_folder !== 'request_additional_info_attachments') {
                    // ファイルのアップロード
                    $upload_data = array(
                        'request_additional_info_id' => $request_additional_info->id,
                        'user_id' => $user->id,
                        'file' => $file
                    );
                    $request_additional_info_attachment = $this->uploadFile($upload_data, 'base64');
                }
            }

            // ログ登録
            $request_log_attributes = [
                'request_id' => $req->request_id,
                'request_work_id' => isset($req->request_work_id) ? $req->request_work_id : null,
                'type' => \Config::get('const.REQUEST_LOG_TYPE.ADDITIONAL_INFO_UPDATED'),
                'created_user_id' => $user->id,
                'updated_user_id' => $user->id,
            ];
            $this->storeRequestLog($request_log_attributes);

            \DB::commit();

            return response()->json([
                'result' => 'success',
                'request_additional_info_id' => $request_additional_info_id,
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
            ]);
        }
    }

    public function delete(Request $req)
    {
        $user = \Auth::user();

        $request_additional_info = RequestAdditionalInfo::find($req->request_additional_info_id);

        // 自分のデータ以外は削除不可
        if ($user->id != $request_additional_info->created_user_id) {
            return response()->json([
                'result' => 'warning',
                'request_additional_info_id' => $request_additional_info->id,
            ]);
        }

        // DB登録
        \DB::beginTransaction();
        try {
            $request_additional_info->is_deleted = 1;
            $request_additional_info->updated_at = Carbon::now();
            $request_additional_info->updated_user_id = $user->id;
            $request_additional_info->save();

            // 依頼補足情報ファイルもdelete
            $request_additional_info_attachments = $request_additional_info->requestAdditionalInfoAttachments;

            foreach ($request_additional_info_attachments as $request_additional_info_attachment) {
                $request_additional_info_attachment->is_deleted = 1;
                $request_additional_info_attachment->updated_at = Carbon::now();
                $request_additional_info_attachment->updated_user_id = $user->id;
                $request_additional_info_attachment->save();
            }

            // ログ登録
            $request_log_attributes = [
                'request_id' => $req->request_id,
                'request_work_id' => isset($req->request_work_id) ? $req->request_work_id : null,
                'type' => \Config::get('const.REQUEST_LOG_TYPE.ADDITIONAL_INFO_DELETED'),
                'created_user_id' => $user->id,
                'updated_user_id' => $user->id,
            ];
            $this->storeRequestLog($request_log_attributes);

            \DB::commit();

            return response()->json([
                'result' => 'success',
                'request_additional_info_id' => $request_additional_info->id,
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
            ]);
        }
    }

    public function deleteAttachment(Request $req)
    {
        $user = \Auth::user();

        $request_additional_info_attachment = RequestAdditionalInfoAttachment::find($req->request_additional_info_attachment_id);

        // 自分のデータ以外は削除不可
        if ($user->id != $request_additional_info_attachment->created_user_id) {
            return response()->json([
                'result' => 'warning',
                'request_additional_info_attachment_id' => $request_additional_info_attachment->id,
            ]);
        }

        // DB登録
        \DB::beginTransaction();
        try {
            $request_additional_info_attachment->is_deleted = 1;
            $request_additional_info_attachment->updated_at = Carbon::now();
            $request_additional_info_attachment->updated_user_id = $user->id;
            $request_additional_info_attachment->save();

            // ログ登録
            $request_log_attributes = [
                'request_id' => $req->request_id,
                'request_work_id' => isset($req->request_work_id) ? $req->request_work_id : null,
                'type' => \Config::get('const.REQUEST_LOG_TYPE.ADDITIONAL_INFO_UPDATED'),
                'created_user_id' => $user->id,
                'updated_user_id' => $user->id,
            ];
            $this->storeRequestLog($request_log_attributes);

            \DB::commit();

            return response()->json([
                'result' => 'success',
                'request_additional_info_attachment_id' => $request_additional_info_attachment->id,
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
                'request_additional_info_attachment_id' => $request_additional_info_attachment->id,
            ]);
        }
    }

    private function uploadFile($data, $type = 'content')
    {
        $request_additional_info_id = $data['request_additional_info_id'];
        $user_id = $data['user_id'];
        $file = $data['file'];

        $file_name = $file['file_name'];
        $file_path = 'request_additional_info_attachments/'. $request_additional_info_id .'/'. Carbon::now()->format('Ymd') .'/'. md5(strval(time())) .'/'. $file_name;
        $file_contents = '';

        switch ($type) {
            case 'base64':
                // file data is decode to base64
                list(, $fileData) = explode(';', $file['file_data']);
                list(, $fileData) = explode(',', $fileData);
                $file_contents = base64_decode($fileData);
                break;
            case 'content':
                $file_contents = $file['file_data'];
                break;
            default:
                throw new \Exception('not_type');
        }

        $s3_file_path = Uploader::uploadToS3($file_contents, $file_path);

        // 登録
        $file = new RequestAdditionalInfoAttachment;
        $file->request_additional_info_id = $request_additional_info_id;
        $file->name = $file_name;
        $file->file_path = $file_path;
        $file->created_user_id = $user_id;
        $file->updated_user_id = $user_id;
        $file->save();

        return $file;
    }
}

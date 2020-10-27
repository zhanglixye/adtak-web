<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Approval;
use App\Models\ApprovalTask;
use App\Models\CreateRequestWorkConfig;
use App\Models\Delivery;
use App\Models\Request as RequestModel;
use App\Models\RequestMail;
use App\Models\RequestMailAttachment;
use App\Models\RequestWork;
use App\Models\SendMail;
use App\Models\SendMailAttachment;
use App\Models\Task;
use App\Models\TaskResult;
use App\Models\TaskResultFile;
use App\Models\ItemConfig;
use App\Models\Queue;
use Illuminate\Http\JsonResponse;
use App\Services\ZipFileManager\ZipService;
use App\Services\DownloadFileManager\Downloader;
use App\Services\UploadFileManager\Uploader;
use Carbon\Carbon;
use League\Flysystem\Exception;
use DB;

class UtilitiesController extends Controller
{
    /**
     * ファイル参照URLを取得する
     *
     * @param String $file_path
     * @param Bool $is_thumbnail
     *
     * @return String
     */
    private function getFileReferenceUrl(String $file_path, Bool $is_thumbnail = false)
    {
        $default_cloud_disk = \Config::get('filesystems.cloud');
        $effective_time = now()->AddMinutes(\Config::get('filesystems.s3_temporary_url_effective_minutes'));

        // default_cloud_disk: S3以外の場合
        if ($default_cloud_disk !== 's3') {
            $disk = \Storage::disk($default_cloud_disk);
            if ($disk->exists($file_path) === false) {
                throw new \RuntimeException('File not found. file_path: ' . $file_path, 404);
            }
            return $disk->url($file_path);
        }

        // default_cloud_disk: S3の場合
        $disk = $is_thumbnail ? \Storage::disk('s3_thumbnail') : \Storage::disk('s3');
        // 動画のthumbnailは画像キャプチャであるため拡張子を書き換え
        if ($is_thumbnail) {
            $extension = pathinfo($file_path, PATHINFO_EXTENSION);
            $required_conversion_extensions = ['mp4', 'm4v', 'gif'];

            if (in_array($extension, $required_conversion_extensions)) {
                $file_path = rtrim($file_path, $extension). 'jpg';
            }
        }
        if ($disk->exists($file_path) === false) {
            throw new \RuntimeException('File not found. file_path: ' . $file_path, 404);
        }
        return $disk->temporaryUrl($file_path, $effective_time);
    }

    public function getFileReferenceUrlForOriginal(Request $req)
    {
        try {
            $file_path = $req->input('file_path');
            $url = self::getFileReferenceUrl($file_path);

            return response()->json([
                'url' => $url,
                'status' => 200
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getFileReferenceUrlForThumbnail(Request $req)
    {
        try {
            $file_path = $req->input('file_path');
            $is_thumbnail = true;
            $url = self::getFileReferenceUrl($file_path, $is_thumbnail);

            return response()->json([
                'url' => $url,
                'status' => 200
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * zipファイルの生成
     *
     * @param Request $req リクエスト
     * @return JsonResponse
     */
    public function createZipFile(Request $req): JsonResponse
    {
        $selected_list = $req->input('selectedList') ? json_decode($req->input('selectedList'), true) : [];

        $managed_tmp_files = [];
        $tmp_local_file_path = null;

        try {
            if (empty($selected_list)) {
                throw new \Exception('No files to download.');
            }
            $disk = \Storage::disk(\Config::get('filesystems.cloud'));
            // 対象のファイルをローカルに一時保存
            foreach ($selected_list as $file) {
                // ファイルのURLを取得
                $file_path = $file['file_path'];

                // 指定ファイルが存在するか確認
                if (!$disk->exists($file_path)) {
                    throw new \Exception('Download is failed. File not exists.');
                }

                // ローカルに一時保存
                $file_name = $file['name'];
                $tmp_disk = \Storage::disk('public');
                $tmp_file_name = str_replace(".", "-", strval(microtime(true))). '-' . $file_name;
                $local_file_path = 'tmp/' . $tmp_file_name;
                $tmp_disk->put($local_file_path, $disk->get($file_path));
                $managed_tmp_files[] = ['file_name' => $file_name, 'local_path' => $local_file_path];
            }
            $zip_file_name = $req->input('zipFileName') ? "{$req->input('zipFileName')}.zip" : 'no_name.zip';//ファイル名が空の状態を防ぐ
            $zip_info = ZipService::compress($managed_tmp_files, 'attachments.zip');
            $tmp_local_file_path = $zip_info['local_path'];
            $local_disk = \Storage::disk('public');
            $local_disk->delete(array_column($managed_tmp_files, 'local_path'));// 内包ファイル

            return response()->json(
                [
                    'status' => 200,
                    'file_path' => $zip_info['local_path'],
                    'file_name' => $zip_file_name
                ]
            );
        } catch (\Exception $e) {
            // 一時ファイルを削除
            $local_disk = \Storage::disk('public');
            $local_disk->delete($tmp_local_file_path);// zip
            $local_disk->delete(array_column($managed_tmp_files, 'local_path'));// 内包ファイル

            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getFilePreviewUrl(Request $req)
    {
        try {
            $file_path = $req->input('file_path');

            $url = self::getFileReferenceUrl($file_path);

            // URL取得時ファイル名がエンコードされていることがあるため、一度デコードを実施
            $file_preview_url = urldecode($url);

            // MIMEタイプを出力
            $mime_type = \Storage::disk(\Config::get('filesystems.cloud'))->mimeType($req->input('file_path'));

            // GoogleのViewerで対応できる拡張子（エクセル、ワード、パワポ、PDF)のMIMETYPEを格納
            $google_support_mime_types = [
                // word
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                // pdf
                'application/pdf',
                // powerpoint
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                // excel
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ];

            // googleのViewerに対応しているか判断
            foreach ($google_support_mime_types as $support_mime_type) {
                if (strcmp($support_mime_type, $mime_type) === 0) {
                    // プレビュー機能にはエンコードされたURLが必要なため、エンコードを実施
                    $file_preview_url = urlencode($file_preview_url);
                    $file_preview_url = 'https://docs.google.com/viewer?url='. $file_preview_url;
                    if ($req->input('is_embed')) {
                        $file_preview_url = $file_preview_url. '&embedded=true';
                    }
                    break;
                }
            }

            return response()->json([
                'status' => 200,
                'file_preview_url' => $file_preview_url
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function fileInfoFromS3(Request $req)
    {
        try {
            $file_path = $req->file_path;
            $file_name = $req->file_name;
            $disk = \Storage::disk(\Config::get('filesystems.cloud'));
            $file = $disk->get($file_path);

            // S3の完全URLを取得
            $url = $disk->url($file_path);
            // S3上に指定ファイルが存在するか確認
            if (!$disk->exists($file_path)) {
                throw new \Exception('S3Download is failed. File not exists');
            }
            $file_name =  isset($file_name) ? $file_name : basename($url);

            // report( $file_path);
            // ローカルに一時保存
            $tmp_disk = \Storage::disk('public');
            $microtime_float = explode('.', (microtime(true)).'.');
            $tmp_file_name = $microtime_float[0]. $microtime_float[1] . $file_name;
            $tmp_disk->put($tmp_file_name, $disk->get($file_path));
            $tmp_file_path = storage_path() . '/app/public/'. $tmp_file_name;
            // $tmp_file_path = storage_path() . '/app/public/'. "test_move1.mp4";
            $mime_type = \File::mimeType($tmp_file_path);
            $file_size = filesize($tmp_file_path);

            $data =  base64_encode(file_get_contents($tmp_file_path));
            $src = 'data:' . $mime_type . ';base64,' . $data;

            // 一時ファイルを削除
            $tmp_disk->delete($tmp_file_name);

            return response()->json([
                'result' => 'success',
                'data' => $src,
                'mime_type' => $mime_type,
                'file_size' => $file_size,
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'result' => 'error',
            ]);
        }
    }

    public static function downloadFromS3(Request $req)
    {
        // 空文字列はnull
        $file_path = !is_null($req->input('file_path')) ? $req->input('file_path') : '';
        $file_name = !is_null($req->input('file_name')) ? $req->input('file_name') : '';

        $disk = \Storage::disk(\Config::get('filesystems.cloud'));

        // 指定ファイルが存在するか確認
        if (!$disk->exists($file_path)) {
            throw new \Exception('Download is failed. File not exists');
        }

        // ファイル名を取得
        $url = $disk->url($file_path);
        $file_name =  $file_name === '' ? $file_name : basename($url);

        // ローカルに一時保存
        $local_disk = \Storage::disk('public');
        $tmp_file_name =  str_replace(".", "-", strval(microtime(true))). '-' . $file_name;
        try {
            // download
            $local_disk->put($tmp_file_name, $disk->get($file_path));// ローカルに保存
            $header = [
                'X-Content-Type-Options' => 'nosniff',//MIME スニッフィングを抑制
            ];
            return response()->download($local_disk->path($tmp_file_name), $file_name, $header)
                ->deleteFileAfterSend(true);//一時ファイルを削除する
        } catch (\Exception $e) {
            $local_disk->delete($tmp_file_name);// 一時ファイルを削除する
            throw $e;// ファイルのDLが行えているように見える問題を回避する
        }
    }

    public static function downloadFromLocal(Request $req)
    {
        // 特にメモリーサイズに引っかからない
        // 空文字列はnull
        $file_path = !is_null($req->input('file_path')) ? $req->input('file_path') : '';
        $file_name = !is_null($req->input('file_name')) ? $req->input('file_name') : '';

        try {
            return Downloader::downloadFromLocal($file_path, $file_name);
        } catch (\Exception $e) {
            report($e);
            throw $e;
        }
    }

    public function getWorkRequestInfo(Request $req)
    {
        try {
            $disk = \Storage::disk(\Config::get('filesystems.cloud'));

            $request_work_id = $req->input('requestWorkId');
            $request_work = RequestWork::findOrFail($request_work_id);
            $request_info = clone $request_work;

            // ====================
            // 依頼メール取得
            // ====================
            // DB構造は1対多だが、依頼に対して依頼メールは1対1
            $request_mails = $request_info->requestMails;
            $request_mail_id = count($request_mails) > 0 ? $request_mails[0]->id : 0;
            $request_mail = null;
            if ($request_mail_id) {
                $request_mail = RequestMail::select([
                    'id',
                    'from',
                    'to',
                    'cc',
                    'bcc',
                    'subject',
                    'content_type',
                    'body',
                    'recieved_at as date'
                ])
                ->find($request_mail_id);

                // 画面表示のために変換
                if ($request_mail->content_type === \Config::get('const.CONTENT_TYPE.TEXT')) {
                    $request_mail->body = nl2br(e($request_mail->body));
                }

                // attachmentsという変数名にしたいため一手間挟んで取得する
                $attachments = RequestMailAttachment::select([
                    'id',
                    'name',
                    'file_path'
                ])
                ->where('request_mail_id', $request_mail->id)->get();

                // ファイルサイズを取得
                foreach ($attachments as $attachment) {
                    $file_size = $disk->size($attachment->file_path);
                    $attachment->file_size = $file_size;
                }
                $request_mail->attachments = $attachments;
            }

            // ====================
            // 依頼ファイル取得
            // ====================
            $request_files = $request_info->requestFiles;
            // DB構造は1対多だが、依頼に対して依頼ファイルは1対1
            $request_file = count($request_files) > 0 ? $request_files[0] : null;

            // ====================
            // 前作業納品取得
            // ====================
            $before_work_id = $request_work->before_work_id;
            $before_work = null;

            if ($before_work_id) {
                $before_work = Delivery::getByRequestWorkId($before_work_id)->toArray();

                $content = json_decode($before_work['content'], true);
                if (empty($content)) {
                    // 納品済みのはずなのに作業内容が取得できていないのはエラー
                    throw new \Exception();
                }

                // request_works.codeが設定されている場合は配列から該当オブジェクトを抽出、置き換える
                if ($request_work->code) {
                    $config = CreateRequestWorkConfig::find($request_work['step_id']);
                    list($group_item, $sub_item) = explode('/', $config['split_items'] . '/');
                    if ($sub_item) {  // $sub_itemが空の場合は不要な処理なので分岐
                        foreach ($content[$group_item] as $obj) {
                            if ($request_work->code == $obj[$sub_item]) {
                                // 対象オブジェクトで置き換え
                                unset($content[$group_item]);
                                $content[$group_item] = $obj;
                            }
                        }
                    }
                    $before_work['content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
                }

                $results = array_key_exists('results', $content) ? $content['results'] : [];
                $send_mail_ids = array_key_exists('mail_id', $results) ? $results['mail_id'] : [];

                if (empty($send_mail_ids)) {
                    // メール以外
                } else {
                    // メール
                    // JSONは配列にしているが1件のみの想定
                    $mail_id = $send_mail_ids[0];
                    $send_mail = SendMail::select([
                        'id',
                        'from',
                        'to',
                        'cc',
                        'bcc',
                        'subject',
                        'content_type',
                        'body',
                        'sended_at as date'
                    ])
                    ->find($mail_id);

                    // 画面表示のために変換
                    if ($send_mail->content_type === \Config::get('const.CONTENT_TYPE.TEXT')) {
                        $send_mail->body = nl2br(e($send_mail->body));
                    }

                    // attachmentsという変数名にしたいため一手間挟んで取得する
                    $attachments = SendMailAttachment::select([
                        'id',
                        'name',
                        'file_path'
                    ])
                    ->where('send_mail_id', $send_mail->id)->get();

                    // ファイルサイズを取得
                    foreach ($attachments as $attachment) {
                        $file_size = $disk->size($attachment->file_path);
                        $attachment->file_size = $file_size;
                    }
                    $send_mail->attachments = $attachments;
                    $before_work['send_mail'] = $send_mail;
                }

                // 作業項目リストを取得
                $items = ItemConfig::getRequestContentsItemList($before_work['step_id'], true);

                // 納品ファイルを取得（本当はdelivery_filesに格納すべきだがまだ対応できていないのでtask_result_filesから取得する）
                $delivery = $request_info->delivery();
                $approval_task = ApprovalTask::find($delivery->approval_task_id);
                $delivered_task_result = TaskResult::latest($approval_task->task_id);
                $delivery_files = TaskResultFile::where('task_result_id', $delivered_task_result->id)->get();

                // deliveries.contentはvalue値としてファイルのseq_noを保持しているため、
                // seq_noをキーとして設定する
                $before_work['files'] = [];
                foreach ($delivery_files as $file) {
                    $before_work['files'][$file->seq_no] = $file;
                }

                $before_work['item_configs'] = [];
                foreach ($items as $item) {
                    $before_work['item_configs'][] = $item;
                }
            }

            return response()->json([
                'request_work' => $request_work,
                'request_mail' => $request_mail,
                'request_file' => $request_file,
                'before_work' => $before_work,
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * サイドメニューに表示する情報を取得する
     *
     * @return String
     */
    public function getSideMenuContents()
    {
        // ユーザー情報取得
        $user = \Auth::user();

        // 管理者権限を所有するか
        $isAdmin = $user->businesses()->count() > 0;

        $incomplete_counts = [];

        // 未作業件数を取得
        $form = [
            'status' => [
                \Config::get('const.TASK_STATUS.NONE'),
                \Config::get('const.TASK_STATUS.ON'),
            ],
        ];
        $incomplete_counts['task'] =  Task::getRelatedDataSetList($user->id, $form, true, true);

        // 以下は管理者のみ
        if ($isAdmin) {
            // 依頼の未完了件数を取得
            $form = [
                'status' => \Config::get('const.REQUEST_STATUS.DOING'),
            ];
            $incomplete_counts['request'] = RequestModel::getRelatedRequestsList($user->id, $form, true);

            // 未割振件数を取得
            $form = [
                'status' => [
                    ['value' => \Config::get('const.ALLOCATION_STATUS.NONE')],
                ],
            ];
            $incomplete_counts['allocation'] = RequestWork::getSearchList($user->id, $form, true);

            // 未承認件数を取得
            $form = [
                'approval_status' => [
                    ['value' => \Config::get('const.APPROVAL_STATUS.NONE')],
                    ['value' => \Config::get('const.APPROVAL_STATUS.ON')],
                ],
            ];
            $incomplete_counts['approval'] = Approval::getList($user->id, $form, true);

            // 未納品件数を取得
            $form = [
                'delivery_status' => [
                    ['value' => \Config::get('const.DELIVERY_STATUS.NONE')],
                    ['value' => \Config::get('const.DELIVERY_STATUS.SCHEDULED')],
                ],
            ];
            $incomplete_counts['delivery'] = Delivery::getSearchList($user->id, $form, true);

            // 不明あり件数を取得
            $form = [
                'task_contact' => true,
            ];
            $incomplete_counts['task_contact'] = Task::getWorkSearchList($user->id, $form, true);
        }

        return response()->json([
            'is_admin' => $isAdmin,
            'incomplete_counts' => $incomplete_counts,
        ]);
    }

    /**
     * メール作成
     * @param Request $req
     * @return JsonResponse
     */
    public function createSendMail(Request $req): JsonResponse
    {
        $user = \Auth::user();

        $form = [
            'to' => $req->input('to'),
            'cc' => $req->input('cc'),
            'subject' => $req->input('subject'),
            'body' => $req->input('body'),
            'attachments' => $req->input('attachments') ? json_decode($req->input('attachments'), true) : [],
        ];

        \DB::beginTransaction();
        try {
            $register_time = Carbon::now();
            $user = \Auth::user();
            $send_mail = new SendMail;
            $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
            $send_mail->to = $form['to'];
            $send_mail->cc = $form['cc'];
            $send_mail->subject =  $form['subject'];
            $send_mail->content_type = \Config::get('const.CONTENT_TYPE.HTML');
            $send_mail->body = $form['body'] === null ? '' : $form['body'];
            $send_mail->created_at = $register_time;
            $send_mail->created_user_id = $user->id;
            $send_mail->updated_at = $register_time;
            $send_mail->updated_user_id = $user->id;
            $send_mail->save();

            // 添付ファイル
            if (is_array($form['attachments'])) {
                foreach ($form['attachments'] as $attachment) {
                    if ($attachment['file_path'] == null) {
                        // ファイルのアップロード
                        $upload_data = array(
                            'file' => $attachment
                        );
                        $file_path = $this->uploadFile($upload_data['file'], 'base64');
                        $attachment['file_path'] = $file_path;
                    }
                    $send_mail_attachment = new SendMailAttachment;
                    $send_mail_attachment->send_mail_id = $send_mail->id;
                    $send_mail_attachment->name = $attachment['file_name'];
                    $send_mail_attachment->file_path = $attachment['file_path'];
                    $send_mail_attachment->created_at = $register_time;
                    $send_mail_attachment->created_user_id = $user->id;
                    $send_mail_attachment->updated_at = $register_time;
                    $send_mail_attachment->updated_user_id = $user->id;
                    $send_mail_attachment->save();
                }
            }

            // 処理キュー登録
            $queue = new Queue;
            $queue->process_type = config('const.QUEUE_TYPE.MAIL_SEND');
            $queue->argument = json_encode(['mail_id' => $send_mail->id]);
            $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
            $queue->created_at = $register_time;
            $queue->created_user_id = $user->id;
            $queue->updated_at = $register_time;
            $queue->updated_user_id = $user->id;
            $queue->save();

            \DB::commit();
            return response()->json([
                'status' => 200
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * upload file is save to s3
     * @param array $file {file_name: string, file_data: base64}
     * @param string $type content | base64
     * @return string file_path
     */
    public static function uploadFile(array $file, string $type = 'content'): string
    {

        $file_name = $file['file_name'];
        $file_path = 'send_mail_attachments/'. Carbon::now()->format('Ymd') .'/'. md5(microtime()) .'/'. $file_name;
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
                throw new Exception('not_type');
        }

        Uploader::uploadToS3($file_contents, $file_path);

        return $file_path;
    }
}

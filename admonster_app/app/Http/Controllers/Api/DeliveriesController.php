<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Traits\RequestLogStoreTrait;
use App\Services\Traits\RequestMailTrait;
use App\Services\Traits\RequestFileTrait;
use App\Services\DestinationManager\DestinationFactory;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\User;
use App\Models\Task;
use App\Models\Approval;
use App\Models\ApprovalTask;
use App\Models\Delivery;
use App\Models\DeliveryFile;
use App\Models\Queue;
use App\Models\RequestWork;
use App\Models\SendMail;
use App\Models\BusinessFlow;
use Illuminate\Http\JsonResponse;
use App\Services\ZipFileManager\ZipService;
use App\Services\FileManager\FileNameManager;
use App\Exceptions\ExclusiveException;
use Carbon\Carbon;

class DeliveriesController extends Controller
{
    use RequestLogStoreTrait;
    use RequestMailTrait;
    use RequestFileTrait;

    public function index(Request $req)
    {
        $user = \Auth::user();

        // 検索条件の取得
        $form = [
            'request_work_ids' => $req->input('request_work_ids'),
            'business_name' => $req->input('business_name'),
            'date_type' => $req->input('date_type'),
            'from' => $req->input('from'),
            'to' => $req->input('to'),
            'client_name' => $req->input('client_name'),
            'worker' => $req->input('worker'),
            'deliverer' => $req->input('deliverer'),
            'subject' => $req->input('subject'),
            'step_name' => $req->input('step_name'),
            'delivery_status' => $req->get('delivery_status'),

            'page' => $req->input('page'),
            'sort_by' => $req->get('sort_by'),
            'descending' => $req->get('descending') ? 'desc' : 'asc',
            'rows_per_page' => $req->get('rows_per_page'),
        ];

        list($list, $all_request_work_ids) = Delivery::getSearchList($user->id, $form);

        // 全ユーザ情報を保持
        $candidates = User::select(
            'users.id',
            'users.name',
            'users.user_image_path'
        )->get();

        return response()->json([
            'list' => $list,             // 納品一覧
            'candidates' => $candidates, // ユーザ情報
            'all_request_work_ids' => $all_request_work_ids //全件作業ID

        ]);
    }

    /**
     * その作業が業務の最終作業かチェックする
     *
     * @param Request $req リクエスト
     * @return JsonResponse
     */
    public function canRedelivery(Request $req): JsonResponse
    {
        try {
            $step_id = $req->input('step_id');
            $business_flow = BusinessFlow::where('step_id', $step_id)->first();
            if (is_null($business_flow)) {
                throw new \Exception('There is no business flow for step ID ' . json_encode($step_id));
            }

            if (is_null($business_flow->next_step_id)) {
                return response()->json([
                    'result' => 'success'
                ]);
            } else {
                return response()->json([
                    'result' => 'warning'
                ]);
            }
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'result' => 'error'
            ]);
        }
    }

    /**
     * 再納品
     *
     * @param Request $req リクエスト
     * @return JsonResponse
     */
    public function redelivery(Request $req): JsonResponse
    {
        // DB登録
        \DB::beginTransaction();
        try {
            $user = \Auth::user();
            $request_work_id = $req->input('request_work_id');

            // deliveryデータの取得
            $approval_task_ids = Approval::where('request_work_id', $request_work_id)->first()
                ->approvalTask()->pluck('id');
            $delivery = Delivery::whereIn('approval_task_id', $approval_task_ids)->first();// 承認と納品のレコードは1:1のため
            if (is_null($delivery)) {
                throw new \Exception('delivery is null');
            }

            // 再納品
            $content = json_decode($delivery->content, true);
            $exists_delivery_item = false;

            // send mail
            if (array_key_exists('mail_id', $content['results']) && count($content['results']['mail_id']) > 0) {
                $exists_delivery_item = true;

                foreach ($content['results']['mail_id'] as $mail_id) {
                    // create queue for send mail
                    $queue = new Queue;
                    $queue->process_type = \Config::get('const.QUEUE_TYPE.MAIL_SEND');
                    $queue->argument = json_encode(["mail_id"=> $mail_id]);
                    $queue->queue_status = \Config::get('const.QUEUE_STATUS.PREVIOUS');
                    $queue->created_user_id = $user->id;
                    $queue->updated_user_id = $user->id;
                    $queue->save();
                }
            }

            // delivery file
            if (array_key_exists('file_seqs', $content['results']) && count(array_keys($content['results']['file_seqs'])) > 0) {
                $exists_delivery_item = true;

                // Confirm delivery destination
                $delivery_destinations = RequestWork::findOrFail($request_work_id)
                    ->step()->first()
                    ->deliveryDestinations()->get();

                // 納品インスタンスの生成
                $instances = [];
                foreach ($delivery_destinations as $delivery_destination) {
                    $instance = DestinationFactory::createDestination($delivery_destination);
                    $instances[$delivery_destination->id] = $instance;
                }

                // ファイルデータを取得
                $disk = \Storage::disk(\Config::get('filesystems.cloud'));
                foreach ($content['results']['file_seqs'] as $delivery_destination_id => $file_seqs) {
                    // ファイルデータ取得
                    $delivery_files = DeliveryFile::where('delivery_id', $delivery->id)
                        ->whereIn('seq_no', $file_seqs)->get();

                    // 納品
                    foreach ($delivery_files as $delivery_file) {
                        $destination_instance = $instances[$delivery_destination_id];
                        if ($destination_instance->getType() !== /*CONST.DESTINATION_TYPE*/ 0) { // システムが使用しているストレージに対して納品はしない
                            $destination_instance->putFile($delivery_file->name, $disk->get($delivery_file->file_path));
                        }
                    }
                }
            }

            if (!$exists_delivery_item) {
                throw new \Exception('Not delivered');
            }

            // 納品日を更新
            $delivery->updated_user_id = $user->id;
            $delivery->save();

            \DB::commit();

            return response()->json([
                'result' => 'success'
            ]);
        } catch (\Exception $e) {
            \DB::rollback();

            report($e);

            return response()->json([
                'result' => 'error'
            ]);
        }
    }

    /**
     * 納品メール・ファイルのzipファイルを作成
     *
     * @param Request $req リクエスト
     * @return JsonResponse
     * @throws \Exception
     */
    public function createZipFile(Request $req): JsonResponse
    {
        $files_to_zip = [];// zipファイルに入れるファイルの情報管理配列 {file_name:string,local_path:string}
        $public_disk = \Storage::disk('public');
        $disk = \Storage::disk(\Config::get('filesystems.cloud'));

        try {
            $request_work_ids = $req->input('request_work_ids', '[]');
            $request_work_ids = json_decode($request_work_ids, true);
            if (is_null($request_work_ids)) {
                throw new \Exception('request work ids is null');
            }

            if (empty($request_work_ids)) {
                throw new \Exception('request work ids is empty');
            }

            $is_only_one = count($request_work_ids) === 1;// 1件のみの選択か

            // 納品メール・ファイルからzipファイルを作成する
            // [deliveries.id => approvals.request_work_id]
            $associative_array = \DB::table('approvals')->whereIn('request_work_id', $request_work_ids)
                ->join('approval_tasks', 'approvals.id', '=', 'approval_tasks.approval_id')
                ->join('deliveries', 'deliveries.approval_task_id', '=', 'approval_tasks.id')
                ->pluck('approvals.request_work_id', 'deliveries.id')
                ->toArray();
            $deliveries = Delivery::whereIn('id', array_keys($associative_array))->get();// $associative_arrayのキーを利用して納品データを取得
            $zip_file_name = '';// 1件の時のzipファイル名

            /** @var array{string:int} 出現したフォルダ名を管理する連想配列 フォルダ名：出現回数 */
            $folder_name_count = [];

            foreach ($deliveries as $delivery) {// 各納品データを処理する
                $content = json_decode($delivery->content, true);// JSON to 連想配列

                // 2件以上の時にフォルダ名に使う名前を取得
                $user_name = '';
                if (!$is_only_one) {
                    $approval_task = $delivery->approvalTask()->first();
                    $task = Task::findOrFail($approval_task->task_id);
                    $user = User::findOrFail($task->user_id);
                    $user_name = FileNameManager::removeSpecialCharacterForWindows($user->name);
                }

                if (isset($content['results']) && !empty($content['results']['mail_id'])) {// 納品メール
                    $mails = SendMail::whereIn('id', $content['results']['mail_id'])->get();
                    foreach ($mails as $mail) {// 基本は1納品では1つのメール
                        // メールはtxtファイルで保存
                        $content = "";
                        $content .= "送信日時:{$mail->sended_at}\n\n";
                        $content .= "from:{$mail->from}\n\n";
                        $content .= "to:{$mail->to}\n\n";
                        $content .= "cc:{$mail->cc}\n\n";
                        $content .= "件名:{$mail->subject}\n\n";
                        $content .= "本文:{$mail->body}";
                        $mail_file_name = 'mail.txt';
                        $tmp_file_name = $this->temporaryFileName($mail_file_name);
                        $public_disk->put($tmp_file_name, $content);

                        $work_folder_name = '';// 2件以上の時に使用される
                        $file_name = '';
                        if ($is_only_one) {// 1件
                            $zip_file_name = FileNameManager::removeSpecialCharacterForWindows($mail->subject);// 作成したメールの件名から特定文字の削除
                            $file_name = $mail_file_name;
                        } else {// 2件以上
                            $mail_subject = FileNameManager::removeSpecialCharacterForWindows($mail->subject);
                            $work_folder_name = "{$mail_subject}_{$user_name}";// 各メールの件名から特定文字の削除
                            $work_folder_name = $this->createManagedFolderName($folder_name_count, $work_folder_name);// 同じファイル名が既にある場合は(n)を付ける
                            $file_name = "{$work_folder_name}/{$mail_file_name}";
                        }
                        $files_to_zip[] = ['file_name' => $file_name, 'local_path' => $tmp_file_name];

                        // mailの添付ファイルをpublicフォルダに一時保存
                        $attachments = $mail->sendMailAttachments()->get();
                        foreach ($attachments as $attachment) {
                            $tmp_file_name = $this->temporaryFileName($attachment->name);
                            $public_disk->put($tmp_file_name, $disk->get($attachment->file_path));
                            $file_name = $is_only_one ? $attachment->name : "{$work_folder_name}/{$attachment->name}";
                            $files_to_zip[] = ['file_name' => $file_name, 'local_path' => $tmp_file_name];
                        }
                    }
                } elseif (isset($content['results']) && isset($content['results']['file_seqs'])) {// 納品ファイル
                    $work_folder_name = FileNameManager::removeSpecialCharacterForWindows(RequestWork::findOrFail($associative_array[$delivery->id])->name);// 依頼作業の件名から特定文字の削除

                    $all_file_seqs = [];
                    foreach ($content['results']['file_seqs'] as $value) {
                        $all_file_seqs = array_merge($all_file_seqs, $value);// 各納品先別の納品ファイル番号を一つの配列に集約
                    }

                    // 納品ファイルをpublicフォルダに一時保存
                    $delivery_files = DeliveryFile::where('delivery_id', $delivery->id)
                        ->whereIn('seq_no', $all_file_seqs)->get();

                    // ファイル名、フォルダ名を設定
                    if ($is_only_one) {// 1件の時
                        $zip_file_name = $work_folder_name;
                    } else {// 2件以上
                        $work_folder_name = "{$work_folder_name}_{$user_name}";
                        $work_folder_name = $this->createManagedFolderName($folder_name_count, $work_folder_name);// 同じファイル名が既にある場合は(n)を付ける
                    }

                    foreach ($delivery_files as $delivery_file) {
                        $file_name = '';
                        if ($is_only_one) {// 1件の時
                            $file_name = $delivery_file->name;
                        } else {// 2件以上
                            $file_name = "{$work_folder_name}/{$delivery_file->name}";
                        }
                        $tmp_file_name = $this->temporaryFileName($delivery_file->name);
                        $public_disk->put($tmp_file_name, $disk->get($delivery_file->file_path));
                        $files_to_zip[] = ['file_name' => $file_name, 'local_path' => $tmp_file_name];
                    }
                }
            }

            // zipファイルの作成
            $zip_file_name = $is_only_one ? "{$zip_file_name}.zip" : 'download.zip';// 2件以上の時は"download.zip"で固定
            $zip_info = ZipService::compress($files_to_zip, $zip_file_name);

            $public_disk->delete(array_column($files_to_zip, 'local_path'));// 一時保存ファイルを削除

            // 作成したzipのパスを返却する
            return response()->json(
                [
                    'result' => 'success',
                    'file_path' => $zip_info['local_path'],
                    'file_name' => $zip_file_name
                ]
            );
        } catch (\Exception $e) {
            $public_disk->delete(array_column($files_to_zip, 'local_path'));// 一時ファイルの削除
            throw $e;
        }
    }

    /**
     * 納品予定メール・ファイルのzipファイルを作成
     *
     * @param Request $req リクエスト
     * @return JsonResponse
     * @throws \Exception
     */
    public function createTaskResultZipFile(Request $req): JsonResponse
    {
        $public_disk = \Storage::disk('public');
        $disk = \Storage::disk(\Config::get('filesystems.cloud'));

        $task_result_id = $req->input('task_result_id');
        $task_result = \DB::table('task_results')->where('id', $task_result_id)->first();
        $content = json_decode($task_result->content, true);
        $mail_ids = array_get($content, 'results.mail_id');

        $files_to_zip = []; //圧縮ファイルリスト
        $zip_file_name = '';

        try {
            if ($mail_ids) {
                $mails = SendMail::whereIn('id', $mail_ids)->get();
                foreach ($mails as $mail) {
                    $content = "";
                    $content .= "送信日時:{$mail->sended_at}\n\n";
                    $content .= "from:{$mail->from}\n\n";
                    $content .= "to:{$mail->to}\n\n";
                    $content .= "cc:{$mail->cc}\n\n";
                    $content .= "件名:{$mail->subject}\n\n";
                    $content .= "本文:{$mail->body}";
                    // メールはtxtファイルで保存
                    $mail_file_name = 'mail.txt';
                    $tmp_file_name = $this->temporaryFileName($mail_file_name);
                    $public_disk->put($tmp_file_name, $content);

                    $files_to_zip[] = ['file_name' => $mail_file_name, 'local_path' => $tmp_file_name];
                    $zip_file_name = FileNameManager::removeSpecialCharacterForWindows($mail->subject).'.zip';

                    // mailの添付ファイルをpublicフォルダに一時保存
                    $attachments = $mail->sendMailAttachments()->get();
                    foreach ($attachments as $attachment) {
                        $tmp_file_name = $this->temporaryFileName($attachment->name);
                        $public_disk->put($tmp_file_name, $disk->get($attachment->file_path));

                        $files_to_zip[] = ['file_name' => $attachment->name, 'local_path' => $tmp_file_name];
                    }
                }
            }

            // zipファイルの作成
            $zip_info = ZipService::compress($files_to_zip, $zip_file_name);
            // 一時保存ファイルを削除
            $public_disk->delete(array_column($files_to_zip, 'local_path'));

            return response()->json([
                'file_path' => $zip_info['local_path'],
                'file_name' => $zip_file_name,
            ]);
        } catch (\Exception $e) {
            // 一時ファイルの削除
            $public_disk->delete(array_column($files_to_zip, 'local_path'));
            throw $e;
        }
    }

    /**
     * 一時ファイル名生成
     * @param string $file_name 一時ファイル名の末に付けるファイル名
     * @return string Unixのタイムスタンプ（マイクロ秒まで） + $file_name
     */
    private function temporaryFileName(string $file_name = ''): string
    {
        $microtime_float = explode('.', (microtime(true)).'.');
        return $microtime_float[0]. $microtime_float[1] . $file_name;
    }

    /**
     * 納品情報の取得
     *
     * @param Request $req リクエスト
     * @return JsonResponse
     * @throws \Exception
     */
    public function deliveryInfo(Request $req): JsonResponse
    {
        $request_work_ids = $req->input('request_work_ids', '[]');
        $request_work_ids = json_decode($request_work_ids, true);
        if (is_null($request_work_ids)) {
            throw new \Exception('request work ids is null');
        }

        if (empty($request_work_ids)) {
            throw new \Exception('request work ids is empty');
        }

        $disk = \Storage::disk(\Config::get('filesystems.cloud'));

        // delivery_id : request_work_id
        $delivery_ids = \DB::table('approvals')
            ->whereIn('request_work_id', $request_work_ids)
            ->join('approval_tasks', 'approvals.id', '=', 'approval_tasks.approval_id')
            ->join('deliveries', 'deliveries.approval_task_id', '=', 'approval_tasks.id')
            ->pluck('approvals.request_work_id', 'deliveries.id')
            ->toArray();

        $deliveries = Delivery::whereIn('id', array_keys($delivery_ids))->get();


        $delivery_info = [];

        foreach ($deliveries as $delivery) {// 各納品データを処理する
            $content = json_decode($delivery->content, true);// JSON to 連想配列

            $size = 0;// 納品ファイルのデータサイズ
            $is_delivered = false;// 納品出来る作業かを判断

            // 納品メール
            if (isset($content['results']) && isset($content['results']['mail_id'])) {
                $is_delivered = true;
                $mails = SendMail::whereIn('id', $content['results']['mail_id'])->get();
                foreach ($mails as $mail) {// 基本は1納品では1つのメール
                    $size += 20000;// メール1通を20KBを固定で追加
                    $attachments = $mail->sendMailAttachments()->get();
                    foreach ($attachments as $attachment) {
                        $size += $disk->size($attachment->file_path);
                    }
                }
            }

            // 納品ファイル
            if (isset($content['results']) && isset($content['results']['file_seqs'])) {
                $is_delivered = true;
                $all_file_seqs = [];
                foreach ($content['results']['file_seqs'] as $value) {
                    $all_file_seqs = array_merge($all_file_seqs, $value);// 各納品先別の納品ファイル番号を一つの配列に集約
                }

                $delivery_files = DeliveryFile::where('delivery_id', $delivery->id)
                    ->whereIn('seq_no', $all_file_seqs)
                    ->get();
                foreach ($delivery_files as $delivery_file) {
                    $size += $disk->size($delivery_file->file_path);
                }
            }

            $delivery_info[$delivery_ids[$delivery->id]] = ['size' => $size, 'is_delivered' => $is_delivered];
        }

        return response()->json(
            [
                'result' => 'success',
                'each_delivery_info' => $delivery_info,
                'limit_size' => 100000000// クライアントのDL容量制限の数値
            ]
        );
    }

    /**
     * フォルダ名が被らないように管理し、文字列を返す
     * @param array{string:int} $folder_name_count 同じ名前が何回出たかをカウントしている連想配列
     * @param string $folder_name 利用しようとしているフォルダ名
     * @return string 作成されたフォルダ名 first:{$folder_name}, other:{$folder_name}(n)
     */
    private function createManagedFolderName(array &$folder_name_count, string $folder_name)
    {
        if (array_key_exists($folder_name, $folder_name_count)) {
            $folder_name_count[$folder_name] += 1;
            return "{$folder_name}({$folder_name_count[$folder_name]})";// {$folder_name}(n)
        }

        // 配列にまだ無い場合は追加
        $folder_name_count[$folder_name] = 0;
        return $folder_name;
    }

    public function changeDeliveryDeadline(Request $req)
    {
        \DB::beginTransaction();
        try {
            $deadline = $req->input('deadline');
            $id = $req->input('id');

            // 排他チェック-------------------------------------------------------
            // ほかのユーザにより更新されているデータを取得

            $request_work = \DB::table('request_works')
                ->selectRaw(
                    'requests.id AS request_id,'.
                    'requests.status AS request_status,'.
                    'request_works.id AS request_work_id'
                )
                ->join('requests', 'request_works.request_id', '=', 'requests.id')
                ->leftJoin('approvals', 'request_works.id', '=', 'approvals.request_work_id')
                ->leftJoin('approval_tasks', function ($join) {
                    $join->on('approval_tasks.approval_id', '=', 'approvals.id');
                })
                ->leftJoin('deliveries', 'deliveries.approval_task_id', '=', 'approval_tasks.id')
                ->where('request_works.id', $id)
                ->where(function ($query) {
                    $query->where('requests.status', '=', \Config::get('const.REQUEST_STATUS.FINISH'))
                    ->orWhere('requests.status', '=', \Config::get('const.REQUEST_STATUS.EXCEPT'))
                    ->orWhere('deliveries.status', \Config::get('const.DELIVERY_STATUS.DONE'));
                })
                ->groupBy(
                    'requests.id',
                    'requests.status',
                    'request_works.id'
                )
                ->count();

            if ($request_work >= 1) {
                return response()->json([
                    'status' => 400
                ]);
            }
            // 排他チェック-------------------------------------------------------

            \DB::table('request_works')
                ->where('id', $id)
                ->update(['deadline' => new Carbon($deadline)]);

            \DB::commit();

            return response()->json([
                'status' => 200
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function changeDeliveryAssignDate(Request $req)
    {
        $user = \Auth::user();

        $delivery_id = $req->input('delivery_id');
        $request_work_id = $req->input('request_work_id');
        $assign_delivery_at = $req->input('assign_delivery_at');
        $updated_at = $req->input('updated_at');

        \DB::beginTransaction();
        try {
            $delivery = Delivery::where('id', $delivery_id)->first();
            // 外部からの更新をチェック
            if ($delivery->updated_at > $updated_at) {
                throw new \Exception("The deliveries data was updated to inactive by another user.", 400);
            }
            // 納品ステータスをチェック
            if ($delivery->status != \Config::get('const.DELIVERY_STATUS.SCHEDULED')) {
                throw new \Exception("The deliveries status is not reserved.", 400);
            }
            // 未指定から納品日を設定する場合
            if ($assign_delivery_at && $delivery->is_assign_date == \Config::get('const.FLG.ACTIVE') && !$delivery->assign_delivery_at) {
                $queue = new Queue;
                $queue->process_type = \Config::get('const.QUEUE_TYPE.WORK_CREATE');
                $queue->argument = json_encode(['request_work_id' => (int)$request_work_id]);
                $queue->queue_status = \Config::get('const.QUEUE_STATUS.PREVIOUS');
                $queue->created_user_id = $user->id;
                $queue->updated_user_id = $user->id;
                $queue->save();
            }
            // 納品日を更新
            $delivery->status = \Config::get('const.DELIVERY_STATUS.SCHEDULED');
            $delivery->is_assign_date = \Config::get('const.FLG.ACTIVE');
            $delivery->assign_delivery_at = new Carbon($assign_delivery_at);
            $delivery->updated_user_id = $user->id;
            $delivery->save();
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
        return response()->json([
            'status' => 200,
        ]);
    }

    /**
     * 納品データの登録
     *
     * @param Request $req リクエスト
     * @throws ExclusiveException
     * @return JsonResponse
     */
    public function store(Request $req)
    {
        $user = \Auth::user();

        $request_work_id = $req->input('request_work_id');
        $content = $req->input('content');
        $started_at = $req->input('started_at');
        $assign_delivery_at = $req->input('assign_delivery_at');

        // DB登録
        \DB::beginTransaction();
        try {
            // 排他チェック-------------------------------------------------------
            // 依頼作業のステータスチェック
            $request_work = RequestWork::find($request_work_id);
            if ($request_work->is_active === \Config::get('const.FLG.INACTIVE') || $request_work->is_deleted === \Config::get('const.FLG.ACTIVE')) {
                throw new ExclusiveException("The request work data was updated to inactive by another user");
            }
            $request_work_count = $request_work->where('updated_at', '>=', $started_at)->count();
            if ($request_work_count > 0) {
                throw new ExclusiveException("The request work data was updated by another user");
            }

            // 依頼のステータスチェック
            $request = RequestModel::find($request_work->request_id);
            if ($request->is_deleted === \Config::get('const.FLG.ACTIVE') || $request->status === \Config::get('const.REQUEST_STATUS.EXCEPT')) {
                throw new ExclusiveException("The request data was updated to inactive by another user");
            }

            // 承認のステータスチェック
            $approval = Approval::where('request_work_id', $request_work_id)->first();
            $approval_updated_dt = new Carbon($approval->updated_at);
            $started_dt = new Carbon($started_at);
            if ($started_dt < $approval_updated_dt) {
                throw new ExclusiveException("The data was updated by another user");
            }

            // 割振更新チェック
            $task = Task::where('request_work_id', $request_work_id)->where('updated_at', '>=', $started_at)->count();
            if ($task > 0) {
                throw new ExclusiveException("The task data was updated by another user");
            }

            // タスクのステータスチェック
            $task_id = $req->input('task_id');
            if (Task::find($task_id)->is_active === \Config::get('const.FLG.INACTIVE')) {
                throw new ExclusiveException("The task to update is disabled");
            }

            // 承認タスクのステータスチェック
            $approval_task = ApprovalTask::where('task_id', $task_id)->first();
            if ($approval_task->approval_result !== \Config::get('const.APPROVAL_RESULT.OK')) {
                throw new ExclusiveException('Can only process approval results from OK. app_task_id=' . json_encode($approval_task->id));
            }
            // /排他チェック------------------------------------------------------

            // 前回と同様の人物による更新の場合でも更新時間を更新
            $request_work->touch();

            // 処理キュー登録（次作業作成）
            $queue = new Queue;
            $queue->process_type = \Config::get('const.QUEUE_TYPE.WORK_CREATE');
            $queue->argument = json_encode(['request_work_id' => (int)$request_work_id]);
            $queue->queue_status = \Config::get('const.QUEUE_STATUS.PREVIOUS');
            $queue->created_user_id = $user->id;
            $queue->updated_user_id = $user->id;
            $queue->save();

            // 納品テーブルへ実作業データを格納
            $delivery = Delivery::where('approval_task_id', $approval_task->id)->first();
            $delivery->updated_user_id = $user->id;
            $delivery->status = \Config::get('const.DELIVERY_STATUS.SCHEDULED');
            $delivery->is_assign_date = \Config::get('const.FLG.ACTIVE');
            $delivery->assign_delivery_at = new Carbon($assign_delivery_at);
            $delivery->save();

            // ログ登録(納品)
            $request_log_attributes = [
                'request_id' => $request_work->request_id,
                'type' => \Config::get('const.REQUEST_LOG_TYPE.DELIVERY_COMPLETED'),
                'request_work_id' => $request_work_id,
                'created_user_id' => $user->id,
                'updated_user_id' => $user->id,
            ];
            $this->storeRequestLog($request_log_attributes);

            \DB::commit();
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => 400,
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status' => 200,
        ]);
    }
}

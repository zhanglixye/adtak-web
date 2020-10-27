<?php


namespace App\Http\Controllers\Api\Biz\Common;

use App\Http\Controllers\Api\Biz\BaseController;
use App\Models\Label;
use App\Models\Queue;
use App\Models\SendMail;
use App\Models\Task;
use App\Models\TaskResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Matrix\Exception;
use Storage;

class DynamicPageController extends BaseController
{
    /** @var string  最後に表示していたページ */
    const CONTENT_NODE_KEY_LAST_DISPLAYED_PAGE = "lastDisplayedPage";


    /**
     * ダイナミック画面.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     */
    public function create(Request $req)
    {
        try {
            $base_info = parent::create($req)->original;

            $task_result_info = MailController::arrayIfNull($base_info, ['task_result_info']);
            if ($task_result_info === null || $task_result_info->content === null) {
                // 1没有作业履历，创建新的作业履历
                // 1-1创建新的作业履历到数据库
                $task_result_po = new TaskResult();
                $task_result_po->task_id = $base_info['task_info']->id;
                $task_result_po->step_id = $base_info['request_info']->step->id;
                $task_result_po->created_user_id = \Auth::user()->id;
                $task_result_po->updated_user_id = \Auth::user()->id;
                // 构造content
                $content_array = [
                    'results' =>
                        [
                            'type' => config('const.TASK_RESULT_TYPE.NOT_WORKING'),
                            'comment' => '',
                            'mail_id' => []
                        ],
                    'lastDisplayedPage' => '1',
                    'components' => [],
                    'values' => [
                    ]
                ];

                $this->initJsonFromDb($content_array['components'], $base_info['request_info']->step->id, null);
                $json_encode = json_encode($content_array);
                $task_result_po->content = $json_encode;
                $task_result_po->save();

                // 1-2 重新构造 task_result_info
                $base_info['task_result_info'] = $task_result_po;
            } else {
                // 1根据作业履历获取文件
                // 1-1反序列化作业履历的content，得到对象
                $content_array = json_decode($task_result_info->content, true);
                // 1-2 重新构造 task_result_info
                $base_info['task_result_info']->content = json_encode($content_array);
            }
            return response()->json($base_info);
        } catch (\Exception $e) {
            report($e);
            return MailController::error('初期化失敗しました。');
        }
    }

    /**
     * 添付ファイルに関する情報を取得する
     * @param Request $req パラメータフォーマット: task_result_file_content={"task_result_id":0, "seq_no_array":[]}
     * @return \Illuminate\Http\JsonResponse ファイルに関する情報
     * @throws \Exception 不正なパラメータ形式または不正なアクセス許可
     */
    public function getTaskResultFileInfoById(Request $req)
    {
        $task_result_query_params = json_decode($req->task_result_file_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return MailController::error("JSONファイルの形式に不備はあります。");
        }
        $task_result_id = (int)MailController::arrayIfNullFail($task_result_query_params, ['task_result_id'], 'JSONファイルの形式に不備はあります。');
        $seq_no_array = MailController::arrayIfNull($task_result_query_params, ['seq_no_array']);
        if (empty($seq_no_array)) {
            return MailController::success([]);
        }

        // タスクは現在のユーザーに属します
        MailController::exclusiveTaskByUserId($req->task_id, \Auth::user()->id, false);

        //タスク実績（ファイル）を取得
        $task_result_file_po_array = MailController::getTaskResultFileByIdArray($task_result_id, $seq_no_array);

        //結果配列を作成する
        $result_array = [];
        foreach ($task_result_file_po_array as $task_result_file_po) {
            $file = [
                'seq_no' => $task_result_file_po->seq_no,
                'file_name' => $task_result_file_po->name,
                'file_path' => $task_result_file_po->file_path,
                'file_size' => $task_result_file_po->size,
                'width' => $task_result_file_po->width,
                'height' => $task_result_file_po->height
            ];
            array_push($result_array, $file);
        }

        return MailController::success($result_array);
    }

    /**
     * s3サーバーへのファイルのアップロードとDBへの保存
     * @param Request $req パラメータフォーマット: task_result_file_content=[{"file_name":"xxx","file_data":"data:image/jpg;base64,xxxxxx"}]
     * @return \Illuminate\Http\JsonResponse ファイルに関する情報
     * @throws \Throwable 不正なパラメータ形式または不正なアクセス許可
     */
    public function uploadFileToS3(Request $req)
    {
        $upload_files = json_decode($req->task_result_file_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return MailController::error("JSONファイルの形式に不備はあります。");
        }

        // タスクは現在のユーザーに属します
        MailController::exclusiveTaskByUserId($req->task_id, \Auth::user()->id, false);
        $ext_info_mixed = MailController::getExtInfoById($req->task_id);
        $business_id = sprintf("B%05d", $ext_info_mixed->business_id);

        // s3サーバーへのファイルのアップロードとDBへの保存
        $task_result_files = MailController::uploadFileAndSave($req->task_id, $business_id, $upload_files);

        //結果配列を作成する
        $result_array = [];
        foreach ($task_result_files as $task_result_file_po) {
            $file = [
                'seq_no' => $task_result_file_po->seq_no,
                'file_name' => $task_result_file_po->name,
                'file_path' => $task_result_file_po->file_path,
                'file_size' => $task_result_file_po->size,
                'width' => $task_result_file_po->width,
                'height' => $task_result_file_po->height
            ];
            array_push($result_array, $file);
        }

        return MailController::success($result_array);
    }

    /**
     * 保留
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function tmpSave(Request $req)
    {
        \DB::beginTransaction();
        try {
            $req_data = json_decode($req->task_result_content, true);
            // 作業時間
            $work_time = MailController::arrayIfNull($req_data, ['work_time']);
            $data = MailController::arrayIfNull($req_data, ['components'], []);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return MailController::error("JSONファイルの形式に不備はあります。");
            }

            // ========================================
            // 排他チェック
            // ========================================
            MailController::exclusiveTask($this->task_id, \Auth::user()->id);

            // ========================================
            // 保存処理
            // ========================================

            // 作業時間
            if ($work_time == null) {
                $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null];
            }
            MailController::defaultWorkTime($work_time, $this->task_id);

            // 最新の作業履歴を取得する
            $task_result_info = TaskResult::with('taskResultFiles')
                ->where('task_id', $this->task_id)
                ->orderBy('id', 'desc')
                ->first();
            if ($task_result_info === null) {
                $task_result_content = [];
            } else {
                $task_result_content = json_decode($task_result_info->content, true);  // 作業内容
            }

            // 最新のタスク実績（ファイル）を取得する
            $task_result_file_no_array = [];
            self::findFileInDynamicJsonArray($data, $task_result_file_no_array);
            $task_result_file_array = MailController::getTaskResultFileByIdArray($task_result_info->id, $task_result_file_no_array);

            //TODO the key('components') need to be moved to the config file
            MailController::arrayKeySet($task_result_content, ['components'], $data);
            $task_result_content['results']['type'] = (int)\Config::get('const.TASK_RESULT_TYPE.HOLD');
            //TODO the last_displayed_page need to be moved to the config file
            $task_result_content['lastDisplayedPage'] = 0;


            // タスクのステータスを対応中に更新
            $task = Task::findOrFail($this->task_id);
            $task->status = config('const.TASK_STATUS.ON');
            $task->updated_user_id = \Auth::user()->id;
            $task->save();

            //値ノードを構築する
            $values_array = [];
            self::makeValueJsonArray($data, $values_array);
            $task_result_content['values'] = $values_array;

            // タスク実績
            $task_result = new TaskResult;
            $task_result->task_id = $this->task_id;
            $task_result->step_id = $this->step_id;
            // 作業時間は手入力値
            // TODO: 手入力する枠が画面にない場合
            $task_result->started_at = is_null($work_time) ? null : $work_time['started_at'];
            $task_result->finished_at = is_null($work_time) ? null : $work_time['finished_at'];
            $task_result->work_time = is_null($work_time) ? null : $work_time['total'];
            $task_result->content = json_encode($task_result_content, JSON_UNESCAPED_UNICODE);
            $task_result->created_user_id = \Auth::user()->id;
            $task_result->updated_user_id = \Auth::user()->id;
            $task_result->save();

            // タスク実績ファイル
            $task_result_file_new = [];
            foreach ($task_result_file_array as $file) {
                array_push($task_result_file_new, $file->replicate());
            }
            MailController::taskFilePersistence($task_result->id, $task_result_file_new);

            \DB::commit();
            return MailController::success();
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return MailController::error('保存失敗しました。');
        }
    }

    /**
     * 处理する
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function process(Request $req)
    {
        \DB::beginTransaction();
        try {
            $req_data = json_decode($req->task_result_content, true);
            // 作業時間
            $work_time = MailController::arrayIfNull($req_data, ['work_time']);
            $data = MailController::arrayIfNull($req_data, ['components'], []);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return MailController::error("JSONファイルの形式に不備はあります。");
            }
            self::inputValidate($data);

            // ========================================
            // 排他チェック
            // ========================================
            MailController::exclusiveTask($this->task_id, \Auth::user()->id);

            // ========================================
            // 保存処理
            // ========================================

            // 作業時間
            if ($work_time == null) {
                $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null];
            }
            MailController::defaultWorkTime($work_time, $this->task_id);


            // 最新の作業履歴を取得する
            $task_result_info = TaskResult::with('taskResultFiles')
                ->where('task_id', $this->task_id)
                ->orderBy('id', 'desc')
                ->first();
            if ($task_result_info === null) {
                $task_result_content = [];
            } else {
                $task_result_content = json_decode($task_result_info->content, true);  // 作業内容
            }

            // 最新のタスク実績（ファイル）を取得する
            $task_result_file_no_array = [];
            self::findFileInDynamicJsonArray($data, $task_result_file_no_array);
            $task_result_file_array = MailController::getTaskResultFileByIdArray($task_result_info->id, $task_result_file_no_array);

            //TODO the key('components') need to be moved to the config file
            MailController::arrayKeySet($task_result_content, ['components'], $data);
            $task_result_content['results']['type'] = (int)\Config::get('const.TASK_RESULT_TYPE.DONE');
            //TODO the last_displayed_page need to be moved to the config file
            $task_result_content['lastDisplayedPage'] = 0;


            // タスクのステータスを対応中に更新
            $task = Task::findOrFail($this->task_id);
            $task->status = config('const.TASK_STATUS.DONE');
            $task->updated_user_id = \Auth::user()->id;
            $task->save();

            //値ノードを構築する
            $values_array = [];
            self::makeValueJsonArray($data, $values_array);
            $task_result_content['values'] = $values_array;
            // タスク実績
            $task_result = new TaskResult;
            $task_result->task_id = $this->task_id;
            $task_result->step_id = $this->step_id;
            // 作業時間は手入力値
            // TODO: 手入力する枠が画面にない場合
            $task_result->started_at = is_null($work_time) ? null : $work_time['started_at'];
            $task_result->finished_at = is_null($work_time) ? null : $work_time['finished_at'];
            $task_result->work_time = is_null($work_time) ? null : $work_time['total'];
            $task_result->content = json_encode($task_result_content, JSON_UNESCAPED_UNICODE);
            $task_result->created_user_id = \Auth::user()->id;
            $task_result->updated_user_id = \Auth::user()->id;
            $task_result->save();

            // タスク実績ファイル
            $task_result_file_new = [];
            foreach ($task_result_file_array as $file) {
                array_push($task_result_file_new, $file->replicate());
            }
            MailController::taskFilePersistence($task_result->id, $task_result_file_new);

            // 処理キュー登録（承認）
            $queue = new Queue;
            $queue->process_type = config('const.QUEUE_TYPE.APPROVE');
            $queue->argument = json_encode(['request_work_id' => $this->request_work_id]);
            $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
            $queue->created_user_id = \Auth::user()->id;
            $queue->updated_user_id = \Auth::user()->id;
            $queue->save();

            // ログ登録
            $request_log_attributes = [
                'type' => \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_NORMALLY'),
                'request_id' => $this->request_id,
                'request_work_id' => $this->request_work_id,
                'task_id' => $this->task_id,
                'created_user_id' => \Auth::user()->id,
                'updated_user_id' => \Auth::user()->id,
            ];
            $this->storeRequestLog($request_log_attributes);

            \DB::commit();
            return MailController::success();
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return MailController::error($e->getMessage());
        }
    }

    /**
     * 不明処理
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function unknown(Request $req)
    {
        \DB::beginTransaction();
        try {
            $req_data = json_decode($req->task_result_content, true);
            $unknown_comment = MailController::arrayIfNull($req_data, ['unknown_comment']);
            // 作業時間
            $work_time = MailController::arrayIfNull($req_data, ['work_time']);
            $data = MailController::arrayIfNull($req_data, ['components'], []);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return MailController::error("JSONファイルの形式に不備はあります。");
            }

            // ========================================
            // 排他チェック
            // ========================================
            MailController::exclusiveTask($this->task_id, \Auth::user()->id);

            // ========================================
            // 保存処理
            // ========================================

            // 作業時間
            if ($work_time == null) {
                $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null];
            }
            MailController::defaultWorkTime($work_time, $this->task_id);

            // 最新の作業履歴を取得する
            $task_result_info = TaskResult::with('taskResultFiles')
                ->where('task_id', $this->task_id)
                ->orderBy('id', 'desc')
                ->first();
            if ($task_result_info === null) {
                $task_result_content = [];
            } else {
                $task_result_content = json_decode($task_result_info->content, true);  // 作業内容
            }

            // 最新のタスク実績（ファイル）を取得する
            $task_result_file_no_array = [];
            self::findFileInDynamicJsonArray($data, $task_result_file_no_array);
            $task_result_file_array = MailController::getTaskResultFileByIdArray($task_result_info->id, $task_result_file_no_array);

            //TODO the key('components') need to be moved to the config file
            MailController::arrayKeySet($task_result_content, ['components'], $data);
            $task_result_content['results']['type'] = (int)\Config::get('const.TASK_RESULT_TYPE.CONTACT');
            //TODO the last_displayed_page need to be moved to the config file
            $task_result_content['lastDisplayedPage'] = 0;


            // メールの登録
            //        関係各位
            //
            //        下記タスクが不備・不明処理となりましたのでかくにんをお願いします。
            //
            //        ■業務名：#{business_name}(#{business_id})
            //        ■作業名：#{task_name}(#{task_id})
            //        ■作業者：#{user_name} #{user_mail_address}
            //        ■コメント内容：#{comment}
            //        ■画面URL：#{url}
            $task_ext_info_po = self::getExtInfoById($this->task_id, \Auth::user()->id);
            $business_id = $task_ext_info_po->business_id;
            $business_name = $task_ext_info_po->business_name;
            $step_name = $task_ext_info_po->step_name;
            $request_id = $task_ext_info_po->request_id;
            $request_work_id = $task_ext_info_po->request_work_id;

            // 获取担当者
            $task_user = \DB::table('tasks')
                ->selectRaw(
                    'users.name user_name,' .
                    'users.email user_email'
                )
                ->join('users', 'tasks.user_id', 'users.id')
                ->where('tasks.id', $this->task_id)
                ->where('users.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
                ->first();
            $task_user_name = $task_user->user_name;
            $task_user_email = $task_user->user_email;

            //「不明あり」で処理します,担当者へのコメント
            $comment = str_replace(array("\r\n", "\r", "\n"), "<br>", $unknown_comment);

            // 画面URL
            $step_count_result = \DB::table('business_flows')
                ->selectRaw(
                    'count(1) cnt'
                )
                ->where('business_flows.business_id', $business_id)
                ->first();
            if ($step_count_result->cnt > 1) {
                $url = \Config::get('app.url') . '/management/request_works/' . $request_work_id;
            } else {
                $url = \Config::get('app.url') . '/management/requests/' . $request_id;
            }

            // 本文生成
            $mail_body = "関係各位<br><br>下記タスクが不備・不明処理となりましたので確認をお願いします。<br><br>■業務名：${business_name}(r${request_id})<br>■作業名：${step_name}(r${request_id}-w${request_work_id})<br>■作業者：${task_user_name} ${task_user_email}<br>■コメント内容：${comment}<br>■画面URL：${url}<br>";

            $send_mail = new SendMail;
            $send_mail->cc = null;
            $send_mail->request_work_id = $task_ext_info_po->request_work_id;
            $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
            $send_mail->subject = "【不明あり】${business_name} ${step_name}";
            $send_mail->body = $mail_body;
            $send_mail->created_user_id = \Auth::user()->id;
            $send_mail->updated_user_id = \Auth::user()->id;
            $send_mail->content_type = \Config::get('const.CONTENT_TYPE.HTML');
            $send_mail->to = self::getUnknownMailTo($business_id);
            $send_mail->save();
            // タスク実績の中に入れる
            $task_result_content['results']['comment'] = $unknown_comment;
            $task_result_content['results']['mail_id'] = $send_mail->id;

            // タスクのステータスを対応中に更新
            $task = Task::findOrFail($this->task_id);
            $task->status = config('const.TASK_STATUS.DONE');
            //ADPORTER_PF-252 各作業画面) 不明点あり時の処理追加 不備・不明（1:あり）
            $task->is_defective = config('const.FLG.ACTIVE');
            $task->updated_user_id = \Auth::user()->id;
            $task->save();

            //値ノードを構築する
            $values_array = [];
            self::makeValueJsonArray($data, $values_array);
            $task_result_content['values'] = $values_array;
            // タスク実績
            $task_result = new TaskResult;
            $task_result->task_id = $this->task_id;
            $task_result->step_id = $this->step_id;
            // 作業時間は手入力値
            // TODO: 手入力する枠が画面にない場合
            $task_result->started_at = is_null($work_time) ? null : $work_time['started_at'];
            $task_result->finished_at = is_null($work_time) ? null : $work_time['finished_at'];
            $task_result->work_time = is_null($work_time) ? null : $work_time['total'];
            $task_result->content = json_encode($task_result_content, JSON_UNESCAPED_UNICODE);
            $task_result->created_user_id = \Auth::user()->id;
            $task_result->updated_user_id = \Auth::user()->id;
            $task_result->save();

            // タスク実績ファイル
            $task_result_file_new = [];
            foreach ($task_result_file_array as $file) {
                array_push($task_result_file_new, $file->replicate());
            }
            MailController::taskFilePersistence($task_result->id, $task_result_file_new);


            // 処理キュー登録（承認）
            $queue = new Queue;
            $queue->process_type = config('const.QUEUE_TYPE.APPROVE');
            $queue->argument = json_encode(['request_work_id' => (int)$task_ext_info_po->request_work_id]);
            $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
            $queue->created_user_id = \Auth::user()->id;
            $queue->updated_user_id = \Auth::user()->id;
            $queue->save();
            // 処理キュー登録（send mail）
            $queue = new Queue;
            $queue->process_type = config('const.QUEUE_TYPE.MAIL_SEND');
            $queue->argument = json_encode(['mail_id' => (int)$send_mail->id]);
            $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
            $queue->created_user_id = \Auth::user()->id;
            $queue->updated_user_id = \Auth::user()->id;
            $queue->save();

            // ログ登録
            $request_log_attributes = [
                'type' => \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_NORMALLY'),
                'request_id' => $this->request_id,
                'request_work_id' => $this->request_work_id,
                'task_id' => $this->task_id,
                'created_user_id' => \Auth::user()->id,
                'updated_user_id' => \Auth::user()->id,
            ];
            $this->storeRequestLog($request_log_attributes);

            \DB::commit();
            return MailController::success();
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return MailController::error('保存失敗しました。');
        }
    }

    /**
     * データベース内のページ構成アイテムを読み取る
     * @param array $json_array ページ構成アイテムを保持する配列への参照
     * @param int $step_id StepId
     * @param int|null $parent_item_key 構成アイテムの親ID(このパラメーターが指定されている場合、親IDに含まれているサブアイテムを照会し、それがnullの場合は最上位アイテムを照会します。)
     */
    private function initJsonFromDb(&$json_array, $step_id, $parent_item_key)
    {
        $item_config_array = self::getItemConfigFromDb($step_id, $parent_item_key);
        if (empty($item_config_array)) {
            return;
        }
        foreach ($item_config_array as $item_config) {
            $layout_option_json_str = $item_config->layout_option;
            $layout_option_json_array = [];
            if (!empty($layout_option_json_str)) {
                $layout_option_json_array = json_decode($layout_option_json_str, true);
            }
            $validate_option_json_str = $item_config->validate_option;
            $validate_option_json_array = [];
            if (!empty($validate_option_json_str)) {
                $validate_option_json_array = json_decode($validate_option_json_str, true);
            }
            $component = [
                'type' => $item_config->item_type,
                'label' => $item_config->label_name,
                'value' => MailController::arrayIfNull($layout_option_json_array, ['value']),
                'tooltip' => null,
                'required' => $item_config->is_required != 0,
                'validate_option' => $validate_option_json_array,
                'format_msg' => null,
                'item_key' => $item_config->item_key,
            ];
            if ($item_config->item_type === 0 //group
                || $item_config->item_type === 300 //radio group
                || $item_config->item_type === 500 //comboBox group
            ) {
                $component['layout'] = MailController::arrayIfNull($layout_option_json_array, ['layout']);
                $urls = self::makeGroupUrls(MailController::arrayIfNull($layout_option_json_array, ['urls']));
                $component['urls'] = $urls;
                $components = array();
                self::initJsonFromDb($components, $step_id, $item_config->id);
                $component['components'] = $components;
            }
            array_push($json_array, $component);
        }
    }

    /**
     * データベースからページの構成アイテムを取得します.
     * @param int $step_id 作業ID
     * @param int|null $parent_item_key 親作業ID(このパラメーターが指定されている場合、親IDに含まれているサブアイテムを照会し、それがnullの場合は最上位アイテムを照会します。)
     * @return array 作業項目設定
     */
    private function getItemConfigFromDb($step_id, $parent_item_key): array
    {
        $sql = ' select item_configs.id, ' .
            '        item_configs.parent_item_key, ' .
            '        item_configs.step_id, ' .
            '        item_configs.sort, ' .
            '        item_configs.label_id, ' .
            '        item_configs.item_key, ' .
            '        item_configs.item_type, ' .
            '        item_configs.layout_option, ' .
            '        item_configs.validate_option, ' .
            '        item_configs.is_required, ' .
            '        item_configs.diff_check_level, ' .
            '        item_configs.is_deleted, ' .
            '        item_configs.created_at, ' .
            '        item_configs.created_user_id, ' .
            '        item_configs.updated_at, ' .
            '        item_configs.updated_user_id, ' .
            '        labels.name label_name' .
            ' from item_configs left join labels ' .
            ' on item_configs.label_id = labels.label_id ' .
            ' where item_configs.is_deleted = 0 ';
        if ($parent_item_key === null) {
            $sql = $sql . ' and item_configs.step_id = ?  and item_configs.parent_item_key is null ';
            $params = [$step_id];
        } else {
            $sql = $sql . ' and item_configs.parent_item_key = ? ';
            $params = [$parent_item_key];
        }
        $sql = $sql . ' order by item_configs.sort ';

        $list = DB::select(
            $sql,
            $params
        );
        return $list;
    }

    /**
     * json配列でファイル番号を検索します
     * @param array $json_array json配列
     * @param array $file_seq_no_array ファイル番号を保存する配列
     */
    private function findFileInDynamicJsonArray(array $json_array, array &$file_seq_no_array)
    {
        foreach ($json_array as $item) {
            $item_type = MailController::arrayIfNull($item, ['type']);
            if ($item_type === 0) { //group
                $this->findFileInDynamicJsonArray(MailController::arrayIfNull($item, ['components'], []), $file_seq_no_array);
            } else if ($item_type === 800) { // file
                $value_array = MailController::arrayIfNull($item, ['value'], []);
                if (is_array($value_array)) {
                    foreach ($value_array as $file_seq_no) {
                        array_push($file_seq_no_array, $file_seq_no);
                    }
                }
            }
        }
    }

    /**
     * 通过TaskId和userId取得Task的关联信息
     * @param int $task_id タスクId
     * @param int $current_user_id ユーザーID
     * @return mixed
     */
    protected function getExtInfoById(int $task_id, int $current_user_id)
    {
        $result = \DB::table('businesses')
            ->selectRaw(
                'businesses.id business_id,' .
                'businesses.name business_name,' .
                'businesses.company_id company_id,' .
                'requests.id request_id,' .
                'request_works.id request_work_id,' .
                'request_works.name request_work_name,' .
                'request_works.step_id step_id,' .
                'steps.name step_name,' .
                'tasks.request_work_id request_work_id'
            )->join('requests', 'businesses.id', '=', 'requests.business_id')
            ->join('request_works', 'requests.id', '=', 'request_works.request_id')
            ->join('tasks', 'request_works.id', '=', 'tasks.request_work_id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->where('tasks.id', $task_id)
            ->where('businesses.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->where('requests.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->where('request_works.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->where('request_works.is_active', \Config::get('const.FLG.ACTIVE'))
            ->where('tasks.is_active', \Config::get('const.FLG.ACTIVE'))->first();
        return $result;
    }

    /**
     * mail to for 不明あり
     * @param int $business_id BusinessID
     * @return string mail to
     */
    private function getUnknownMailTo(int $business_id)
    {
        //業務の管理者
        $mail_to = '';
        $business_admin_result = \DB::table('businesses_candidates')
            ->selectRaw(
                'users.id user_id,' .
                'users.email user_email'
            )->join('users', 'businesses_candidates.user_id', '=', 'users.id')
            ->where('businesses_candidates.business_id', $business_id)
            ->where('users.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->get();
        foreach ($business_admin_result as $admin) {
            if (!empty($mail_to)) {
                $mail_to = $mail_to . ',';
            }
            $mail_to = $mail_to . $admin->user_email;
        }
        return $mail_to;
    }

    private function makeValueJsonArray(array $json_array, array &$value_json_array)
    {
        foreach ($json_array as $item) {
            $item_type = MailController::arrayIfNull($item, ['type']);
            $item_key = MailController::arrayIfNull($item, ['item_key']);
            $value = MailController::arrayIfNull($item, ['value']);


            if (!empty($item_key)) {
                $value_json_array[$item_key] = $value;
            }
            if ($item_type === 0 //group
                || $item_type === 300 //radio group
                || $item_type === 500 //comboBox group
            ) { //group
                $this->makeValueJsonArray(MailController::arrayIfNull($item, ['components'], []), $value_json_array);
            }
        }
    }

    private function inputValidate(array $json_array, String $parent_label = '')
    {
        foreach ($json_array as $item) {
            $item_type = MailController::arrayIfNull($item, ['type']);
            $value = MailController::arrayIfNull($item, ['value']);
            $required = MailController::arrayIfNull($item, ['required'], false);
            $label = MailController::arrayIfNull($item, ['label']);
            $validate_option_array = MailController::arrayIfNull($item, ['validate_option']);
            if ($item_type === 310 //combobox item
                || $item_type === 510 //Radio item
                || $item_type === 610 //SelectBox item
                || $item_type === 1000 //link
            ) {
                continue;
            }
            if ($item_type === 0) { //group
                $parent_label_new = '';
                if (!empty($label)) {
                    $parent_label_new = $label;
                    if (!empty($parent_label)) {
                        $parent_label_new = $parent_label_new . '`|`' . $parent_label;
                    }
                }
                $this->inputValidate(MailController::arrayIfNull($item, ['components'], []), $parent_label_new);
            } else {
                if (empty($label)) {
                    //The current item has no title, it's parent title is used.
                    $parent_label_array = (explode('`|`', $parent_label));
                    if (!empty($parent_label_array)) {
                        $label = $parent_label_array[0];
                    }
                }
                if ($required) {
                    $this->requiredValidate($value, $label);
                }
                if (!empty($validate_option_array)) {
                    $this->maxLengthCharValidate($validate_option_array, $value, $label);
                    $this->formatValidate($validate_option_array, $value, $label);
                }
            }
        }
    }

    private function requiredValidate($value, $label): void
    {
        if (empty($value)) {
            if ($value === 0 || $value === 0.0 || $value === '0') {
                //0 is not empty
            } else {
                throw new \Exception("${label}の入力は必須です");
            }
        }
    }

    private function maxLengthCharValidate($validate_option_array, $value, $label): void
    {
        $max_length_char = MailController::arrayIfNull($validate_option_array, ['max_length_char']);
        if ($max_length_char != null) {
            if (!is_array($value) && !is_object($value)) {
                $chars = mb_strlen(strval($value));
                if ($chars > ((int)$max_length_char)) {
                    throw new \Exception("${label}の入力内容は項目桁数定義と一致しません。${max_length_char}文字まで入力可能です");
                }
            }
        }
    }

    private function formatValidate($validate_option_array, $value, $label): void
    {
        $format_pattern = MailController::arrayIfNull($validate_option_array, ['format_pattern']);
        if (!empty($format_pattern)) {
            $format_pattern_decode = base64_decode($format_pattern);
            if (!is_array($value) && !is_object($value) && !self::emptyNotZero($value)) {
                if (!preg_match($format_pattern_decode, strval($value))) {
                    throw new \Exception("${label}の入力内容は項目型定義と一致しません");
                }
            }
        }
    }

    private function emptyNotZero($value)
    {
        if (empty($value)) {
            if ($value === 0 || $value === 0.0 || $value === '0') {
                //0 is not empty
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    private function makeGroupUrls($urls_array)
    {
        if (empty($urls_array)) {
            return null;
        }
        $result_array = [];
        foreach ($urls_array as $url) {
            $label_id = MailController::arrayIfNull($url, ['label_id']);
            if (!empty($label_id)) {
                $url['label'] = Label::where('label_id', $label_id)->value('name');
            }
            array_push($result_array, $url);
        }
        return $result_array;
    }
}

<?php

namespace App\Http\Controllers\Api\Biz;

use Carbon\Carbon;

use Illuminate\Http\Request;

use App\Exceptions\ExclusiveException;
use App\Http\Controllers\Controller;
use App\Services\Traits\RequestLogStoreTrait;
use App\Services\Traits\RequestFileTrait;
use App\Services\Traits\RequestMailTrait;

use App\Models\Queue;
use App\Models\RequestWork;
use App\Models\Task;
use App\Models\TaskResult;
use App\Models\Request as RequestModel;
use App\Models\RequestFile;
use App\Models\RequestMail;
use App\Models\Label;
use App\Models\User;

class BaseController extends Controller
{
    use RequestLogStoreTrait;
    use RequestFileTrait;
    use RequestMailTrait;

    public $step_id;
    public $request_id;
    public $request_work_id;
    public $task_id;
    public $task_started_at;
    public $user;
    public $result;
    public $err_message;
    public $task_result_content;
    public $work_time;

    public function __construct(Request $req)
    {
        $this->step_id = $req->step_id;
        $this->request_id = $req->request_id;
        $this->request_work_id = $req->request_work_id;
        $this->task_id = $req->task_id;
        $this->task_started_at = $req->task_started_at;
        $this->result = 'warning';
        $this->err_message = '';
    }

    public function create(Request $req)
    {
        $task_info = Task::findOrFail($this->task_id);
        // 最新の作業履歴
        $task_result_info = TaskResult::with('taskResultFiles')
            ->where('task_id', $this->task_id)
            ->orderBy('id', 'desc')
            ->first();

        $request_work = RequestWork::findOrFail($this->request_work_id);
        $request_info = clone $request_work;

        // 依頼メール
        if (count($request_work->requestMails) >= 1) {
            $mail_id = $request_work->requestMails[0]->id;
            $request_info->request_mail = $this->tryCreateMailTemplateData($mail_id);
        }

        // 依頼ファイル
        $request_file = RequestFile::getWithPivotByRequestWorkId($this->request_work_id);
        $label_data = new \stdClass();
        if (!is_null($request_file)) {
            $file_import_configs = $this->getFileImportConfigs($request_work->step_id);
            $column_configs = $file_import_configs['column_configs'];

            // 依頼内容情報
            if ($request_file->content) {
                $request_file->content = $this->generateRequestFileItemData($column_configs, $request_file->content);
            }
            $request_info->request_file = $request_file;

            // ラベルデータ
            $label_ids = [];
            foreach ($column_configs as $column_config) {
                $label_ids[] = $column_config->label_id;
            }
            $label_data = Label::getLangKeySetByIds($label_ids);
        }

        $business_name = $request_work->request->business->name;
        $step_name = $request_work->step->name;

        // 全ユーザ情報を保持（途中で担当者から外れたケースにも対応するため）
        $candidates = User::get();

        return response()->json([
            'business_name' => $business_name,
            'step_name' => $step_name,
            'task_started_at' => Carbon::now()->format('Y/m/d H:i:s'),
            'task_info' => $task_info,
            'label_data' => $label_data,
            'task_result_info' => $task_result_info,
            'request_info' => $request_info,
            'candidates' => $candidates,
        ]);
    }

    public function store(Request $req)
    {
        $this->user = \Auth::user();
        \DB::beginTransaction();

        try {
            // ========================================
            // 排他チェック
            // ========================================
            $this->exclusive();

            // ========================================
            // 保存処理
            // ========================================
            $task_result_content = json_decode($req->task_result_content, true);  // 作業内容
            if ($task_result_content['results']['type'] === \Config::get('const.TASK_RESULT_TYPE.DONE')) {
                // 完了
                $this->save($task_result_content);
            } elseif ($task_result_content['results']['type'] === \Config::get('const.TASK_RESULT_TYPE.CONTACT')) {
                // 問い合わせ（不明あり）
                $this->contact($task_result_content);
            } elseif ($task_result_content['results']['type'] === \Config::get('const.TASK_RESULT_TYPE.HOLD')) {
                // 一時保存（対応中）
                $this->temporarySave($task_result_content);
            }

            $this->result = 'success';
            \DB::commit();
        } catch (\Exception $e) {
            $this->result = 'error';
            $this->err_message = $e;
            \DB::rollback();
            report($e);
        }

        return response()->json([
            'result' => $this->result,
            'err_message' => $this->err_message,
        ]);
    }

    public function exclusive()
    {
        // tasksが更新可能な状態であることを確認
        $task = Task::findOrFail($this->task_id)
            ->where('status', '<>', \Config::get('const.TASK_STATUS.DONE'))
            ->where('is_active', \Config::get('const.FLG.ACTIVE'))
            ->where('updated_at', '<', $this->task_started_at);
    }

    public function save(array $content, array $work_time = null)
    {
        // タスクのステータスを完了に更新
        $task = Task::findOrFail($this->task_id);
        $task->status = config('const.TASK_STATUS.DONE');
        $task->updated_user_id = $this->user->id;
        $task->save();

        // タスク実績
        $task_result = new TaskResult;
        $task_result->task_id = $this->task_id;
        $task_result->step_id = $this->step_id;
        // 作業時間は手入力値
        // TODO: 手入力する枠が画面にない場合
        $task_result->started_at = is_null($work_time) ? null : $work_time['started_at'];
        $task_result->finished_at = is_null($work_time) ? null : $work_time['finished_at'];
        $task_result->work_time = is_null($work_time) ? null : $work_time['total'];
        $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $task_result->created_user_id = $this->user->id;
        $task_result->updated_user_id = $this->user->id;
        $task_result->save();

        // 処理キュー登録（承認）
        $queue = new Queue;
        $queue->process_type = config('const.QUEUE_TYPE.APPROVE');
        $queue->argument = json_encode(['request_work_id' => $this->request_work_id]);
        $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
        $queue->created_user_id = $this->user->id;
        $queue->updated_user_id = $this->user->id;
        $queue->save();

        // ログ登録
        $request_log_attributes = [
            'type' => \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_NORMALLY'),
            'request_id' => $this->request_id,
            'request_work_id' => $this->request_work_id,
            'task_id' => $this->task_id,
            'created_user_id' => $this->user->id,
            'updated_user_id' => $this->user->id,
        ];
        $this->storeRequestLog($request_log_attributes);
    }

    public function contact(array $content, array $work_time = null)
    {
        // タスクのステータスを完了に更新
        $task = Task::findOrFail($this->task_id);
        $task->status = config('const.TASK_STATUS.DONE');
        $task->updated_user_id = $this->user->id;
        $task->save();

        // タスク実績
        $task_result = new TaskResult;
        $task_result->task_id = $this->task_id;
        $task_result->step_id = $this->step_id;
        // 作業時間は手入力値
        // TODO: 手入力する枠が画面にない場合
        $task_result->started_at = is_null($work_time) ? null : $work_time['started_at'];
        $task_result->finished_at = is_null($work_time) ? null : $work_time['finished_at'];
        $task_result->work_time = is_null($work_time) ? null : $work_time['total'];
        $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $task_result->created_user_id = $this->user->id;
        $task_result->updated_user_id = $this->user->id;
        $task_result->save();

        // 処理キュー登録（承認）
        $queue = new Queue;
        $queue->process_type = config('const.QUEUE_TYPE.APPROVE');
        $queue->argument = json_encode(['request_work_id' => $this->request_work_id]);
        $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
        $queue->created_user_id = $this->user->id;
        $queue->updated_user_id = $this->user->id;
        $queue->save();

        // ログ登録
        $request_log_attributes = [
            'type' => \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_WITH_UNCLEAR_POINT'),
            'request_id' => $this->request_id,
            'request_work_id' => $this->request_work_id,
            'task_id' => $this->task_id,
            'created_user_id' => $this->user->id,
            'updated_user_id' => $this->user->id,
        ];
        $this->storeRequestLog($request_log_attributes);
    }

    public function temporarySave(array $content, array $work_time = null)
    {
        // タスクのステータスを対応中に更新
        $task = Task::findOrFail($this->task_id);
        $task->status = config('const.TASK_STATUS.ON');
        $task->updated_user_id = $this->user->id;
        $task->save();

        // タスク実績
        $task_result = new TaskResult;
        $task_result->task_id = $this->task_id;
        $task_result->step_id = $this->step_id;
        // 作業時間は手入力値
        // TODO: 手入力する枠が画面にない場合
        $task_result->started_at = is_null($work_time) ? null : $work_time['started_at'];
        $task_result->finished_at = is_null($work_time) ? null : $work_time['finished_at'];
        $task_result->work_time = is_null($work_time) ? null : $work_time['total'];
        $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $task_result->created_user_id = $this->user->id;
        $task_result->updated_user_id = $this->user->id;
        $task_result->save();
    }

    /**
     * 補足情報や関連メールなどまとめたページャーを取得
     *
     * @param Request $req
     */
    public function getAppendices(Request $req)
    {
        $request_id = $req->request_id;
        $search_params = $req->search_params;
        $types = array_get($search_params, 'types');

        $request = RequestModel::where('id', $request_id)
            ->with([
                'relatedMails' => function ($query) {
                    $query->where('is_open_to_work', \Config::get('const.FLG.ACTIVE'));
                },
                'relatedMails.requestMailAttachments',
                'requestAdditionalInfos' => function ($query) {
                    $query->where('is_open_to_work', \Config::get('const.FLG.ACTIVE'));
                    $query->where('is_deleted', \Config::get('const.FLG.INACTIVE'));
                    $query->orderBy('created_at', 'desc');
                },
                'requestAdditionalInfos.requestAdditionalInfoAttachments' => function ($query) {
                    $query->where('is_deleted', \Config::get('const.FLG.INACTIVE'));
                }
            ])->first();

        $disk = \Storage::disk(\Config::get('filesystems.cloud'));

        $appendices = collect();
        // 関連メール
        if (!$types || in_array(\Config::get('const.APPENDICES_TYPE.RELATED_MAIL'), $types)) {
            $related_mails = $request->relatedMails;
            foreach ($related_mails as &$related_mail) {
                $related_mail->original_body = $related_mail->body;
                // 表示用に変換
                if ($related_mail->content_type === RequestMail::CONTENT_TYPE_HTML) {
                    $related_mail->body = strip_tags($related_mail->body, '<br>');
                } else {
                    $related_mail->body = nl2br(e($related_mail->body));
                }

                // ファイルサイズを取得
                foreach ($related_mail->requestMailAttachments as $mail_attachment) {
                    $file_size = $disk->size($mail_attachment->file_path);
                    $mail_attachment->file_size = $file_size;
                }
                $related_mail->mail_attachments = $related_mail->requestMailAttachments;
            }
            $appendices = $appendices->merge($related_mails);
        }
        // 補足情報
        if (!$types || in_array(\Config::get('const.APPENDICES_TYPE.ADDITIONAL'), $types)) {
            $additional_infos = $request->requestAdditionalInfos;
            foreach ($additional_infos as $additional_info) {
                // ファイルサイズを取得
                foreach ($additional_info->requestAdditionalInfoAttachments as $mail_attachment) {
                    $file_size = $disk->size($mail_attachment->file_path);
                    $mail_attachment->file_size = $file_size;
                }
                $additional_info->mail_attachments = $additional_info->requestAdditionalInfoAttachments;
            }
            $appendices = $appendices->merge($additional_infos);
        }
        $appendices = $appendices->sortByDesc('updated_at')->values();
        $appendices = json_decode(json_encode($appendices));

        // ページ番号が指定されていなければ１ページ目
        $page_num = isset($search_params['page']) ? $search_params['page'] : 1;
        $per_page = isset($search_params['rows_per_page']) ? $search_params['rows_per_page'] : 5;
        $disp_rec = array_slice($appendices, ($page_num - 1) * $per_page, $per_page);
        // ページャーオブジェクトを生成
        $pager = new \Illuminate\Pagination\LengthAwarePaginator(
            $disp_rec, // ページ番号で指定された表示するレコード配列
            count($appendices), // 検索結果の全レコード総数
            $per_page, // 1ページ当りの表示数
            $page_num, // 表示するページ
            ['path' => $req->url()] // ページャーのリンク先のURL
        );

        return response()->json([
            'appendices' => $pager,
        ]);
    }
}

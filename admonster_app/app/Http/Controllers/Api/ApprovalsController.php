<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Traits\RequestLogStoreTrait;
use App\Services\Traits\RequestFileTrait;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\RequestWork;
use App\Models\RequestFile;
use App\Services\Traits\RequestMailTrait;
use App\Models\User;
use App\Models\Approval;
use App\Models\Task;
use App\Models\ApprovalTask;
use App\Models\Delivery;
use App\Models\Queue;
use App\Models\Label;
use App\Models\ItemConfig;
use App\Models\TaskResultFile;
use App\Models\SendMail;
use Carbon\Carbon;
use DB;
use function GuzzleHttp\json_encode;
use function GuzzleHttp\json_decode;
use App\Exceptions\ExclusiveException;

class ApprovalsController extends Controller
{
    use RequestLogStoreTrait;
    use RequestFileTrait;
    use RequestMailTrait;

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
            'approver' => $req->input('approver'),
            'subject' => $req->input('subject'),
            'step_name' => $req->input('step_name'),
            'approval_status' => $req->get('approval_status'),

            'page' => $req->input('page'),
            'sort_by' => $req->get('sort_by'),
            'descending' => $req->get('descending') ? 'desc' : 'asc',
            'rows_per_page' => $req->get('rows_per_page'),
        ];

        $list = Approval::getList($user->id, $form);

        // 全ユーザ情報を保持
        $candidates = User::select(
            'users.id',
            'users.name',
            'users.user_image_path'
        )->get();

        return response()->json([
            'status' => \Config::get('const.API_STATUS_CODE.SUCCESS'),
            'list' => $list,             // 承認一覧
            'candidates' => $candidates, // ユーザ情報
        ]);
    }

    public function edit(Request $req)
    {
        $user = \Auth::user();

        $request_work_id = $req->input('request_work_id');

        // ------------------------------
        // 依頼情報をセット
        $approval = Approval::where('request_work_id', $request_work_id)->first();
        $sql = DB::table('approvals')
            ->select(
                DB::raw($approval->status . ' as process'),
                'businesses.name as business_name',
                'requests.id as id',
                'requests.name as request_name',
                'requests.status as request_status',
                'requests.is_deleted as request_is_deleted',
                'request_works.id as request_work_id',
                'request_works.is_active as request_work_is_active',
                'request_works.updated_at as request_work_updated_at',
                'steps.id as step_id',
                'steps.url as step_url',
                'tasks.user_id as approved_user_id',
                'request_mails.id as mail_id'
            )
            ->join('request_works', 'approvals.request_work_id', '=', 'request_works.id')
            ->join('requests', 'request_works.request_id', '=', 'requests.id')
            ->join('businesses', 'requests.business_id', '=', 'businesses.id')
            ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->leftJoin('approval_tasks', 'approvals.id', '=', 'approval_tasks.approval_id')
            ->leftJoin('deliveries', 'approval_tasks.id', '=', 'deliveries.approval_task_id')
            ->leftJoin('approval_tasks as delivered_approval_tasks', 'delivered_approval_tasks.id', '=', 'deliveries.approval_task_id')
            ->leftJoin('tasks', function ($sub) {
                $sub->on('delivered_approval_tasks.task_id', '=', 'tasks.id')
                    ->where('tasks.is_education', \Config::get('const.FLG.INACTIVE'));
            })
            ->leftJoin('request_work_mails', 'request_works.id', '=', 'request_work_mails.request_work_id')
            ->leftJoin('request_mails', 'request_work_mails.request_mail_id', '=', 'request_mails.id')
            ->where('request_works.id', $request_work_id)
            ->where('businesses_admin.user_id', $user->id);
        if ($approval->status != \Config::get('const.APPROVAL_STATUS.DONE')) {
            // 未承認
            $sql->whereNull('tasks.user_id');
        }
        $request = $sql->first();

        // 依頼メール
        if (!is_null($request->mail_id)) {
            // メール情報の取得
            $request_mail_template_info = $this->tryCreateMailTemplateData($request->mail_id);
            $request->request_mail = $request_mail_template_info;
        }

        // 依頼ファイル
        $request_file = RequestFile::getWithPivotByRequestWorkId($request_work_id);
        $label_data = new \stdClass();
        if (!is_null($request_file)) {
            $file_import_configs = $this->getFileImportConfigs($request->step_id);
            $column_configs = $file_import_configs['column_configs'];
            // 依頼内容情報
            if ($request_file->content) {
                $request_file->content = $this->generateRequestFileItemData($column_configs, $request_file->content);
            }
            $request->request_file = $request_file;
        }

        // 2019/10/31 ラベル情報はすべて渡して、画面内で操作する方が良いがデータ量などを考慮した対応が必要
        // Commit ID:d1439d08e3f972e558af85578db4bf8365b9837d
        // ラベル情報の全件取得
        $label_data = Label::getLangKeySetAll();

        // ------------------------------
        // 該当作業の項目を取得する

        // 指定したstep_idで重複したsort番号は除外（ID番号が高い方を優先）
        $ids_for_about_step = ItemConfig::select(DB::raw('MAX(id) as id'))
            ->where('step_id', $request->step_id)
            ->where('is_deleted', \Config::get('const.FLG.INACTIVE'))// 削除していない
            ->groupBy('sort')
            ->pluck('id');

        $items = ItemConfig::select(
            'id',
            'item_key',
            'item_type',
            'label_id',
            'option'
        )
            ->whereIn('id', $ids_for_about_step)
            ->orderBy('sort', 'asc')
            ->get();

        $tmp = array();
        $item_configs = array();
        foreach ($items as $item) {
            $item_keys = explode('/', $item->item_key);
            $item->group = $item_keys[0];
            $item->key = end($item_keys);
            $item->option = is_null($item->option) ? new \stdClass() : json_decode($item->option);
            $item->item_config_values = $item->itemConfigValues()
                ->select('label_id', 'sort')
                ->where('is_deleted', \Config::get('const.FLG.INACTIVE'))
                ->get();
            $tmp[$item_keys[0]][] = $item;
        }

        foreach ($tmp as $obj) {
            $item_configs[] = $obj;
        }

        // ------------------------------
        // 実作業内容を取得
        $sub = \DB::table('task_results')
            ->select(\DB::raw('max(task_results.id) as task_result_id'))
            ->groupBy('task_results.task_id');

        $query = \DB::table('tasks')
            ->select(
                'tasks.is_active as is_active',
                'tasks.status as status',
                'task_results.id as task_result_id',
                'tasks.id as task_id',
                'task_results.step_id as step_id',
                'task_results.finished_at as finished_at',
                'task_results.content as content',
                'tasks.request_work_id as request_work_id',
                'users.id as user_id',
                'users.name as user_name',
                'users.user_image_path as user_image_path'
            )
            ->join('users', 'tasks.user_id', '=', 'users.id')
            ->leftJoin('task_results', 'tasks.id', '=', 'task_results.task_id')
            ->where('tasks.request_work_id', $request_work_id)
            // 最新のタスクの結果と未実施のタスクを取得するための条件
            ->where(function ($query) use ($sub) {
                $query->whereIn('task_results.id', $sub->pluck('task_result_id'))
                    ->orWhereNull('task_results.id');
            })
            ->where('tasks.is_education', '=', \Config::get('const.FLG.INACTIVE'))
            ->orderBy('task_results.id', 'desc');

        // 完了済作業を取得する
        $user_ids = $req->input('user_ids');
        $user_ids = $user_ids ? explode(',', $user_ids) : [];

        if ($user_ids) {
            $query = $query->whereIn('users.id', $user_ids);
        }

        $task_results = $query->get();

        foreach ($task_results as $task_result) {
            if (is_null($task_result->content)) {
                continue;
            }
            $content = json_decode($task_result->content, true);

            // 不要なキーを削除
            if (array_key_exists('values', $content)) {
                $content_values = $content['values'];
                unset($content['values']);
                $content = array_merge($content, $content_values);
            }
            if (array_key_exists('components', $content)) {
                unset($content['components']);
            }

            // 不明ありメール送信ID
            $mail_id = array_get($content, 'results.mail_id.0');

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
            ])->find($mail_id);

            // 画面表示のために変換
            if ($send_mail && $send_mail->content_type === \Config::get('const.CONTENT_TYPE.TEXT')) {
                $send_mail->body = nl2br(e($send_mail->body));
            }
            $task_result->contact_mail = $send_mail;
            $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }

        $items = array(
            '800' => [],
            '801' => []
        );
        foreach ($item_configs as $array) {
            foreach ($array as $value) {
                if ($value->item_type === 800) {
                    $items['800'][] = $value;
                }
                if ($value->item_type === 801) {
                    $items['801'][] = $value;
                }
            }
        }

        // ---C00800用の処理
        foreach ($items['800'] as $item) {
            foreach ($task_results as $task_result) {
                if (is_null($task_result->content)) {
                    continue;
                }
                $content = json_decode($task_result->content, true); // 連想配列で取り出す
                if (strcmp($item->group, $item->item_key) == 0) { // 第一階層
                    if (array_key_exists($item->item_key, $content)) {
                        $data = $content[$item->item_key];
                        $content[$item->item_key] = $this->createDataInTheFormC00800($task_result->task_result_id, $data);
                    }
                } else { // 第二階層
                    if (array_key_exists($item->group, $content)) {
                        if (array_key_exists($item->key, $content[$item->group])) {
                            $data = $content[$item->group][$item->key];
                            $content[$item->group][$item->key] = $this->createDataInTheFormC00800($task_result->task_result_id, $data);
                        } elseif (is_array($content[$item->group])) {
                            foreach ($content[$item->group] as &$value) {
                                if (array_key_exists($item->key, $value)) {
                                    $data = $value[$item->key];
                                    $value[$item->key] = $this->createDataInTheFormC00800($task_result->task_result_id, $data);
                                }
                            }
                            unset($value);
                        }
                    }
                }
                $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
            }
        }
        // ---C00800用の処理

        // ---C00801用の処理
        foreach ($items['801'] as $item) {
            foreach ($task_results as $task_result) {
                if (is_null($task_result->content)) {
                    continue;
                }

                $content = json_decode($task_result->content, true); // 連想配列で取り出す
                if (strcmp($item->group, $item->item_key) == 0) { // 第一階層
                    if (array_key_exists($item->item_key, $content)) {
                        $file_id = $content[$item->item_key];
                        if (is_null($file_id)) {
                            continue;
                        }
                        $content[$item->item_key] = $this->createDataInTheFormC00801($task_result->task_result_id, $file_id);
                    }
                } else { // 第二階層
                    if (array_key_exists($item->group, $content)) {
                        if (array_key_exists($item->key, $content[$item->group])) {// グループの中に1つある場合
                            $file_id = $content[$item->group][$item->key];
                            if (is_null($file_id)) {
                                continue;
                            }
                            $content[$item->group][$item->key] = $this->createDataInTheFormC00801($task_result->task_result_id, $file_id);
                        } elseif (is_array($content[$item->group])) {// グループに複数の場合
                            foreach ($content[$item->group] as $key => $group) {
                                if (array_key_exists($item->key, $group)) {
                                    $file_id = $group[$item->key];
                                    if (is_null($file_id)) {
                                        $content[$item->group][$key][$item->key] = null;
                                        continue;
                                    }
                                    $content[$item->group][$key][$item->key] = $this->createDataInTheFormC00801($task_result->task_result_id, $file_id);
                                }
                            }
                        }
                    }
                }
                $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
            }
        }
        // ---C00801用の処理

        // 承認タスクを取得
        $approval = Approval::where('request_work_id', $request_work_id)->first();
        $approval_tasks = null;
        if (isset($approval)) {
            $approval_tasks = ApprovalTask::where('approval_id', $approval->id)->get();
        }

        // 作業担当の候補者を取得
        $business_id = RequestModel::find(RequestWork::find($request_work_id)->request_id)->business_id;
        $businesses_candidates = \DB::table('users')
            ->select(
                'users.id as id',
                'users.name as name',
                'users.user_image_path as user_image_path'
            )
            ->join('businesses_candidates', function ($join) use ($business_id) {
                $join->on('users.id', '=', 'businesses_candidates.user_id')
                ->where('users.is_deleted', '=', \Config::get('const.FLG.INACTIVE'))
                ->where('businesses_candidates.business_id', '=', $business_id);
            })
            ->get();

        // 業務管理者か判断
        $request->is_business_admin = true;

        return response()->json([
            'status' => \Config::get('const.API_STATUS_CODE.SUCCESS'),
            'request' => $request,
            'item_configs' => $item_configs,
            'task_results' => $task_results,
            'approval_tasks' => isset($approval_tasks)? $approval_tasks: [],
            'businesses_candidates' => $businesses_candidates,
            'label_data' => $label_data,
        ]);
    }

    public function store(Request $req)
    {
        $user = \Auth::user();

        $request_id = $req->input('request_id');
        $request_work_id = $req->input('request_work_id');
        $approval_status = $req->input('approval_status');
        $result_type = $req->input('result_type');
        $content = $req->input('content');
        $request_work_updated_at = $req->input('request_work_updated_at');

        // DB登録
        \DB::beginTransaction();
        try {
            // 排他チェック-------------------------------------------------------
            $request_work = RequestWork::find($request_work_id);
            if ($request_work->where('updated_at', $request_work_updated_at)->doesntExist()) {
                throw new ExclusiveException("The request work data was updated.");
            }

            if ($request_work->is_active === \Config::get('const.FLG.INACTIVE') || $request_work->is_deleted === \Config::get('const.FLG.ACTIVE')) {
                throw new ExclusiveException("The request work data was updated to inactive by another user");
            }

            $request = RequestModel::find($request_work->request_id);
            if ($request->is_deleted === \Config::get('const.FLG.ACTIVE') || $request->status === \Config::get('const.REQUEST_STATUS.EXCEPT')) {
                throw new ExclusiveException("The request data was updated to inactive by another user");
            }

            $approval = Approval::where('request_work_id', $request_work_id)->first();
            if ($approval->status === \Config::get('const.APPROVAL_STATUS.DONE')) {
                throw new ExclusiveException("Approval completed");
            }
            // /排他チェック------------------------------------------------------

            // 承認結果「保留」の場合
            if ($approval_status == \Config::get('const.APPROVAL_STATUS.ON')) {
                // 承認レコードの更新
                $approval->request_work_id = $request_work_id;
                $approval->status = $approval_status;
                $approval->result_type = $result_type;
                $approval->updated_user_id = $user->id;
                $approval->save();

                // 前回と同様の人物による更新の場合でも更新時間を更新
                $approval->touch();
                $request_work->touch();

                // 個別の承認結果
                $input_approval_tasks = json_decode($req->input('approval_tasks'), true);

                // 排他チェック-------------------------------------------------------

                $task_ids = [];
                foreach ($input_approval_tasks as $approval_task) {
                    array_push($task_ids, $approval_task['task_id']);
                }

                // 更新する対象のタスクが無効化されていた際はエラー
                $is_show_warning = Task::whereIn('id', $task_ids)
                    ->where('is_active', \Config::get('const.FLG.INACTIVE'))
                    ->count() > 0;
                if ($is_show_warning) {
                    throw new ExclusiveException("The task to update is disabled");
                }
                // /排他チェック------------------------------------------------------

                // 承認タスクの追加or更新
                foreach ($input_approval_tasks as $input_approval_task) {
                    ApprovalTask::updateOrCreate(
                        ['task_id' => $input_approval_task['task_id']],
                        [
                            'approval_result' => $input_approval_task['approval_result'],
                            'approval_id' => $approval->id,
                            'task_id' => $input_approval_task['task_id'],
                            'updated_user_id' => $user->id,
                            'created_user_id' => array_key_exists('created_user_id', $input_approval_task) ? $input_approval_task['created_user_id'] : $user->id
                        ]
                    );
                }
            } else {
                // 排他チェック-------------------------------------------------------
                // 更新する対象のタスクが無効化されていた際はエラー
                $task_id = $req->input('task_id');
                if (Task::find($task_id)->is_active === \Config::get('const.FLG.INACTIVE')) {
                    throw new ExclusiveException("The task to update is disabled");
                }
                // /排他チェック------------------------------------------------------

                // 承認レコードの更新
                $approval->request_work_id = $request_work_id;
                $approval->status = $approval_status;
                $approval->result_type = $result_type;
                $approval->updated_user_id = $user->id;
                $approval->save();

                // 前回と同様の人物による更新の場合でも更新時間を更新
                $approval->touch();
                $request_work->touch();

                // ログ登録(承認)
                $request_log_attributes = [
                    'request_id' => $request_id,
                    'type' => \Config::get('const.REQUEST_LOG_TYPE.APPROVAL_COMPLETED'),
                    'request_work_id' => $request_work_id,
                    'created_user_id' => $user->id,
                    'updated_user_id' => $user->id,
                ];
                $this->storeRequestLog($request_log_attributes);

                // 個別の承認結果
                $input_approval_tasks = json_decode($req->input('approval_tasks'), true);
                // 承認タスクの追加or更新
                foreach ($input_approval_tasks as $input_approval_task) {
                    ApprovalTask::updateOrCreate(
                        ['task_id' => $input_approval_task['task_id']],
                        [
                            'approval_result' => $input_approval_task['approval_result'],
                            'approval_id' => $approval->id,
                            'task_id' => $input_approval_task['task_id'],
                            'updated_user_id' => $user->id,
                            'created_user_id' => array_key_exists('created_user_id', $input_approval_task) ? $input_approval_task['created_user_id'] : $user->id
                        ]
                    );
                }
                $approval_task = ApprovalTask::where('task_id', $task_id)
                    ->where('approval_result', \Config::get('const.APPROVAL_RESULT.OK'))
                    ->firstOrFail();

                // 関連タスクをすべて無効化
                Task::where('request_work_id', $request_work_id)
                    ->where('status', '<>', \Config::get('const.TASK_STATUS.DONE'))
                    ->update(['is_active' => \Config::get('const.FLG.INACTIVE')]);

                // 即時納品の場合
                $delivery_later = $req->input('delivery_later');
                if (!$delivery_later) {
                    // 処理キュー登録（次作業作成）
                    $queue = new Queue;
                    $queue->process_type = \Config::get('const.QUEUE_TYPE.WORK_CREATE');
                    $queue->argument = json_encode(['request_work_id' => (int)$req->request_work_id]);
                    $queue->queue_status = \Config::get('const.QUEUE_STATUS.PREVIOUS');
                    $queue->created_user_id = $user->id;
                    $queue->updated_user_id = $user->id;
                    $queue->save();

                    // ログ登録(納品)
                    $request_log_attributes = [
                        'request_id' => $request_id,
                        'type' => \Config::get('const.REQUEST_LOG_TYPE.DELIVERY_COMPLETED'),
                        'request_work_id' => $request_work_id,
                        'created_user_id' => $user->id,
                        'updated_user_id' => $user->id,
                    ];
                    $this->storeRequestLog($request_log_attributes);
                }

                // 納品テーブルへ実作業データを格納
                $delivery = new Delivery;
                $delivery->approval_task_id = $approval_task->id;
                $delivery->content = $content;
                $delivery->created_user_id = $user->id;
                $delivery->updated_user_id = $user->id;
                $delivery->status = $delivery_later ? \Config::get('const.DELIVERY_STATUS.NONE') : \Config::get('const.DELIVERY_STATUS.SCHEDULED');
                $delivery->is_assign_date = $delivery_later ? \Config::get('const.FLG.ACTIVE') : \Config::get('const.FLG.INACTIVE');
                $delivery->save();
            }

            \DB::commit();

            return response()->json([
                'status' => \Config::get('const.API_STATUS_CODE.SUCCESS'),
                'request_work' => $request_work
            ]);
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => \Config::get('const.API_STATUS_CODE.EXCLUSIVE'),
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
    }

    public function againTask(Request $req)
    {
        $request_work_id = $req->input('request_work_id');
        $message = empty($req->input('message')) ? '' : $req->input('message');
        $task_id = $req->input('task_id');
        $request_work_updated_at = $req->input('request_work_updated_at');

        // DB登録
        \DB::beginTransaction();
        try {
            // 排他チェック-------------------------------------------------------
            $task = Task::find($task_id);
            if ($task->is_active === \Config::get('const.FLG.INACTIVE')) {
                throw new ExclusiveException("The task to update is disabled");
            }

            $request_work = RequestWork::find($request_work_id);
            if ($request_work->where('updated_at', $request_work_updated_at)->doesntExist()) {
                throw new ExclusiveException("The request work data was updated by another user");
            }

            if ($request_work->is_active === \Config::get('const.FLG.INACTIVE') || $request_work->is_deleted === \Config::get('const.FLG.ACTIVE')) {
                throw new ExclusiveException("The request work data work was updated to inactive by another user");
            }

            $request = RequestModel::find($request_work->request_id);
            if ($request->is_deleted === \Config::get('const.FLG.ACTIVE') || $request->status === \Config::get('const.REQUEST_STATUS.EXCEPT')) {
                throw new ExclusiveException("The request data was updated to inactive by another user");
            }

            $approval = Approval::where('request_work_id', $request_work_id)->first();
            if ($approval->status === \Config::get('const.APPROVAL_STATUS.DONE')) {
                throw new ExclusiveException("Approval completed");
            }
            // /排他チェック------------------------------------------------------

            // 前回と同様の人物による更新の場合でも更新時間を更新
            $approval->touch();
            $request_work->touch();

            $user = \Auth::user();

            // タスクの無効化
            $inactive_task = Task::find($task_id);
            $inactive_task->fill([
                'is_active' => \Config::get('const.FLG.INACTIVE'),
                'updated_user_id' => $user->id
            ]);
            $inactive_task->save();

            // タスクの追加
            $newTask = new Task;
            $newTask->fill([
                'request_work_id' => $inactive_task->request_work_id,
                'user_id' => $inactive_task->user_id,
                'status' => \Config::get('const.TASK_STATUS.NONE'),
                'message' => $message,
                'deadline' => $request_work->deadline,
                'system_deadline' => $request_work->deadline,
                'is_active' => \Config::get('const.FLG.ACTIVE'),
                'created_user_id' => $user->id,
                'updated_user_id' => $user->id
            ]);
            $newTask->save();

            // ログ登録(作業の戻し)
            $request_log_attributes = [
                'request_id' => $request_work->request_id,
                'type' => \Config::get('const.REQUEST_LOG_TYPE.STEPS_RETURNED'),
                'request_work_id' => $request_work_id,
                'task_id' => $task_id,
                'created_user_id' => $user->id,
                'updated_user_id' => $user->id,
            ];
            $this->storeRequestLog($request_log_attributes);

            \DB::commit();

            return response()->json([
                'status' => \Config::get('const.API_STATUS_CODE.SUCCESS'),
                'request_work' => $request_work
            ]);
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => \Config::get('const.API_STATUS_CODE.EXCLUSIVE'),
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
    }

    public function allocate(Request $req)
    {
        $request_work_id = $req->input('request_work_id');
        $add_user_id = $req->input('add_user_id');
        $request_work_updated_at = $req->input('request_work_updated_at');

        \DB::beginTransaction();
        try {
            // 排他チェック-------------------------------------------------------
            $request_work = RequestWork::find($request_work_id);
            if ($request_work->where('updated_at', $request_work_updated_at)->doesntExist()) {
                throw new ExclusiveException("The request work data was updated by another user");
            }

            if ($request_work->is_active === \Config::get('const.FLG.INACTIVE') || $request_work->is_deleted === \Config::get('const.FLG.ACTIVE')) {
                throw new ExclusiveException("The request work data work was updated to inactive by another user");
            }

            // Request
            $request = RequestModel::find($request_work->request_id);
            if ($request->is_deleted === \Config::get('const.FLG.ACTIVE') || $request->status === \Config::get('const.REQUEST_STATUS.EXCEPT')) {
                throw new ExclusiveException("The request data was updated to inactive by another user");
            }

            // Approval
            $approval = Approval::where('request_work_id', $request_work_id)->first();
            if ($approval->status === \Config::get('const.APPROVAL_STATUS.DONE')) {
                throw new ExclusiveException("Approval completed");
            }
            // /排他チェック------------------------------------------------------

            // 承認レコードの更新時間を更新
            $approval->touch();
            $request_work->touch();

            // add task
            $user = \Auth::user();
            $current_time  = Carbon::now();
            $insert_task = [
                'request_work_id' => $request_work_id,
                'user_id' => $add_user_id,
                'status' => \Config::get('const.TASK_STATUS.NONE'),
                'deadline' => $request_work->deadline,
                'system_deadline' => $request_work->deadline,
                'is_active' => \Config::get('const.FLG.ACTIVE'),
                'created_at' => $current_time,
                'created_user_id' => $user->id,
                'updated_at' => $current_time,
                'updated_user_id' => $user->id
            ];
            Task::insert($insert_task);

            // ログ登録(割振)
            $request_log_attributes = [
                'request_id' => $request_work->request_id,
                'type' => \Config::get('const.REQUEST_LOG_TYPE.ALLOCATION_COMPLETED'),
                'request_work_id' => $request_work_id,
                'created_user_id' => $user->id,
                'updated_user_id' => $user->id,
            ];
            $this->storeRequestLog($request_log_attributes);

            \DB::commit();

            return response()->json([
                'status' => \Config::get('const.API_STATUS_CODE.SUCCESS'),
                'request_work' => $request_work
            ]);
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => \Config::get('const.API_STATUS_CODE.EXCLUSIVE'),
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
    }

    private function createDataInTheFormC00800(int $task_result_id, array $task_result_file_ids): array
    {
        // 必要な項目はselectで宣言
        $task_result_files = TaskResultFile::select('seq_no', 'name', 'file_path', 'size', 'created_at')
            ->where('task_result_id', $task_result_id)
            ->whereIn('seq_no', $task_result_file_ids)
            ->get();

        $disk = \Storage::disk(\Config::get('filesystems.cloud'));
        foreach ($task_result_files as $value) {
            // ファイルサイズを取得
            if (is_null($value->size)) {
                $value->size = $disk->size($value->file_path);
            }

            // ハッシュの生成
            $path = '';
            if (\Config::get('filesystems.cloud') === 's3') {
                $path = $disk->temporaryUrl(
                    $value->file_path,
                    now()->addMinutes(1)
                );
            } else {
                $path = $disk->path($value->file_path);
            }
            $value->hash = hash_file('sha512', $path);
        }

        return $task_result_files->toArray();
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

    private function createDataInTheFormC00801(int $task_result_id, int $task_result_file_id): object
    {
        // 必要な項目はselectで宣言
        $task_result_file = TaskResultFile::select('seq_no', 'name', 'file_path', 'size', 'created_at')
            ->where('task_result_id', $task_result_id)
            ->where('seq_no', $task_result_file_id)
            ->first();

        $disk = \Storage::disk(\Config::get('filesystems.cloud'));
        // ファイルサイズを取得
        if (is_null($task_result_file->size)) {
            $task_result_file->size = $disk->size($task_result_file->file_path);
        }
        // mimeTypeを取得
        $task_result_file->mime_type = $disk->mimetype($task_result_file->file_path);

        // ハッシュの生成
        $path = '';
        if (\Config::get('filesystems.cloud') === 's3') {
            $path = $disk->temporaryUrl(
                $task_result_file->file_path,
                now()->addMinutes(1)
            );
        } else {
            $path = $disk->path($task_result_file->file_path);
        }
        $task_result_file->hash = hash_file('sha512', $path);

        return $task_result_file;
    }
}

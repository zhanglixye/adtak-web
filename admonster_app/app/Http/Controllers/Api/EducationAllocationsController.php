<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Traits\RequestLogStoreTrait;
use App\Services\Traits\RequestFileTrait;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Request as RequestModel;
use App\Models\Task;
use App\Models\User;
use App\Services\Traits\RequestMailTrait;
use App\Models\RequestWork;
use App\Models\RequestFile;
use App\Models\Label;
use DB;

class EducationAllocationsController extends Controller
{
    use RequestLogStoreTrait;
    use RequestFileTrait;
    use RequestMailTrait;
    use \App\Services\Traits\SearchFormTrait;

    public function index(Request $req)
    {
        // 検索条件の取得
        $form = [
            'request_work_ids' => $req->input('request_work_ids'),
            'business_name' => $req->input('business_name'),
            'date_type' => $req->input('date_type'),
            'from' => $req->input('from'),
            'to' => $req->input('to'),
            'client_name' => $req->input('client_name'),
            'worker' => $req->input('worker'),
            'allocator' => $req->input('allocator'),
            'subject' => $req->input('subject'),
            'step_name' => $req->input('step_name'),
            'status' => $req->get('status'),
            'has_not_working' => $req->get('has_not_working'),

            'page' => $req->input('page'),
            'sort_by' => $req->get('sort_by'),
            'descending' => $req->get('descending') ? 'desc' : 'asc',
            'rows_per_page' => $req->get('rows_per_page'),
        ];

        $user = \Auth::user();

        // 各request_workに関連する最新のtask.idを取得
        $max_task_id_of_each_request_work = \DB::table('tasks')
            ->selectRaw('MAX(tasks.id) AS id')
            ->groupBy('tasks.request_work_id');

        // 各request_workの割振担当者を取得
        $allocator_sql = \DB::table('users')
            ->selectRaw(
                'users.id AS id,'.
                'users.`name` AS `name`,'.
                'users.user_image_path AS user_image_path,'.
                'tasks.request_work_id AS request_work_id'
            )
            ->join('tasks', function ($join) use ($max_task_id_of_each_request_work) {
                $join->on('tasks.created_user_id', '=', 'users.id')
                    ->whereRaw('tasks.is_education = ?', [\Config::get('const.FLG.ACTIVE')])
                    ->whereRaw('tasks.id IN '.'(' . $max_task_id_of_each_request_work->toSql(). ')');
            });

        // 割振一覧を取得
        $query = \DB::table('request_works')
            ->leftJoin('tasks', function ($sub) {
                $sub->on('request_works.id', '=', 'tasks.request_work_id')
                    ->where('tasks.is_active', '<>', \Config::get('const.FLG.INACTIVE'))
                    ->where('tasks.is_education', '=', \Config::get('const.FLG.ACTIVE'));
            })
            ->join('approvals', 'approvals.request_work_id', '=', 'request_works.id')
            ->join('approval_tasks', 'approval_tasks.approval_id', '=', 'approvals.id')
            ->join('deliveries', 'deliveries.approval_task_id', '=', 'approval_tasks.id')
            ->leftJoin('request_work_mails', 'request_works.id', '=', 'request_work_mails.request_work_id')
            ->leftJoin('request_mails', 'request_work_mails.request_mail_id', '=', 'request_mails.id')
            ->leftJoin('request_work_files', 'request_works.id', '=', 'request_work_files.request_work_id')
            ->leftJoin('request_files', 'request_work_files.request_file_id', '=', 'request_files.id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->join('allocation_configs', 'steps.id', '=', 'allocation_configs.step_id')
            ->leftJoin('users as worker', 'tasks.user_id', '=', 'worker.id')
            ->leftJoinSub($allocator_sql, 'allocator', function ($join) {
                $join->on('allocator.request_work_id', '=', 'request_works.id');
            })
            ->join('requests', 'request_works.request_id', '=', 'requests.id')
            ->join('businesses', 'requests.business_id', '=', 'businesses.id')
            ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->join('companies', 'businesses.company_id', '=', 'companies.id')
            ->where('businesses_admin.user_id', $user->id);

        // 依頼作業ID
        if (is_array($form['request_work_ids']) && !empty($form['request_work_ids'])) {
            $query = $query->whereIn('request_works.id', $form['request_work_ids']);
        }

        // 業務名
        if ($form['business_name']) {
            // プレフィックス判定
            $prefix = substr($form['business_name'], 0, 1);
            if (config('const.MASTER_ID_PREFIX.TABLE_COLUMN.'. $prefix)) {
                // ID検索
                $query = self::getMasterIdSearchQuery($query, $form['business_name']);
            } else {
                $query = $query->where('businesses.name', 'LIKE', '%'.$form['business_name'].'%');
            }
        }
        // 依頼主
        if ($form['client_name']) {
            $query = $query->where('request_works.client_name', 'LIKE', '%'.$form['client_name'].'%');
        }
        // 担当者
        if ($form['worker']) {
            // 1,全角スペースを半角スペースへ変換
            $worker_names = str_replace('　', ' ', $form['worker']);
            // 2,前後のスペースを削除
            $worker_names = trim($worker_names);
            // 3,連続する半角スペースを半角スペースひとつに変換
            $worker_names = preg_replace('/\s+/', ' ', $worker_names);
            // 4,半角スペースで分割
            $worker_names = explode(' ', $worker_names);

            $query = $query->whereIn(
                'request_works.id',
                function ($sub) use ($worker_names) {
                    $sub->select('tasks.request_work_id as request_work_id')
                        ->from('tasks')
                        ->join('users', 'tasks.user_id', '=', 'users.id')
                        ->where('tasks.is_education', '=', \Config::get('const.FLG.ACTIVE'));

                    foreach ($worker_names as $key => $value) {
                        if ($key === 0) {
                            $sub->where('users.name', 'LIKE', '%'.$value.'%');
                        } else {
                            $sub->orWhere('users.name', 'LIKE', '%'.$value.'%');
                        }
                    }
                }
            );
        }
        // 割振担当者
        if ($form['allocator']) {
            $query = $query->where('allocator.name', 'LIKE', '%'.$form['allocator'].'%');
        }
        // 件名
        if ($form['subject']) {
            $query = $query->where('request_works.name', 'LIKE', '%'.$form['subject'].'%');
        }
        // 作業名
        if ($form['step_name']) {
            $query = $query->where('steps.name', 'LIKE', '%'.$form['step_name'].'%');
        }

        if ($form['date_type'] == \Config::get('const.DATE_TYPE.CREATED')) {
            // 発生日
            if ($form['from'] && $form['to']) {
                $query = $query->whereBetween('request_works.created_at', [$form['from'], $form['to']]);
            } elseif ($form['from'] && !$form['to']) {
                $query = $query->where('request_works.created_at', '>=', $form['from']);
            } elseif (!$form['from'] && $form['to']) {
                $query = $query->where('request_works.created_at', '<=', $form['to']);
            }
        } else {
            // 設定期限
            if ($form['from'] && $form['to']) {
                $query = $query->whereBetween('request_works.deadline', [$form['from'], $form['to']]);
            } elseif ($form['from'] && !$form['to']) {
                $query = $query->where('request_works.deadline', '>=', $form['from']);
            } elseif (!$form['from'] && $form['to']) {
                $query = $query->where('request_works.deadline', '<=', $form['to']);
            }
        }

        // タスクステータス
        if (is_array($form['status']) && !empty($form['status'])) {
            $status = array_column($form['status'], 'value');
            $query = $query->where(function ($query) use ($status) {
                if (in_array(\Config::get('const.ALLOCATION_STATUS.NONE'), $status)
                    && in_array(\Config::get('const.ALLOCATION_STATUS.DONE'), $status)) {
                    // 絞り込み無し
                } elseif (in_array(\Config::get('const.ALLOCATION_STATUS.NONE'), $status)) {
                    //「未割振」はNULLで絞り込み
                    $query->whereNull('tasks.status');
                } elseif (in_array(\Config::get('const.ALLOCATION_STATUS.DONE'), $status)) {
                    // 「割振済」はNOT NULLで絞り込み
                    $query->whereNotNull('tasks.status');
                }
            });
        }

        // 未作業ありを表示
        if ($form['has_not_working']) {
            $query->havingRaw('GROUP_CONCAT(DISTINCT tasks.status) <> ?', [\Config::get('const.TASK_STATUS.DONE')]);
        }

        // 除外対象
        $query = $query->where('companies.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('businesses.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('steps.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('requests.status', '<>', \Config::get('const.REQUEST_STATUS.EXCEPT'));
        $query = $query->where('request_works.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('request_works.is_active', \Config::get('const.FLG.ACTIVE'));

        $query = $query->groupBy(
            'requests.id',
            'request_works.id',
            'request_works.name',
            'request_works.client_name',
            'request_works.created_at',
            'request_works.request_id',
            'request_mails.id',
            'request_files.id',
            'steps.id',
            'businesses.id',
            'requests.id',
            'approvals.status',
            'allocator.id',
            'allocator.name',
            'allocator.user_image_path'
        );

        // 全件作業IDを取得する
        $all_request_work_ids = $query->pluck('request_works.id');

        // 割振一覧用にselect句を指定
        $query->selectRaw(
            'companies.name AS company_name,'.
            'businesses.name AS business_name,'.
            'requests.id AS request_id,'.
            'request_works.id AS request_work_id,'.
            'request_works.`name` AS request_work_name,'.
            'request_works.client_name AS client_name,'.
            'request_works.created_at AS created_at,'.
            'request_works.deadline AS deadline,'.
            'request_mails.subject AS subject,'.
            'request_mails.from AS request_mail_from,'.
            'request_mails.to AS request_mail_to,'.
            'LEFT(request_mails.body,'.\Config::get('const.REQUEST_MAIL_BODY_MAX_LENGTH_ON_LIST').') AS request_mail_body,'.
            '(SELECT COUNT(request_mail_attachments.id)'.
                ' FROM request_mail_attachments'.
                ' WHERE request_mail_attachments.request_mail_id = request_mails.id'.
            ') as mail_attachments_count,'.
            'steps.id AS step_id,'.
            'steps.name AS step_name,'.
            'allocation_configs.parallel AS parallel,'.
            '0 AS is_auto,'.
            'allocation_configs.method AS allocation_method,'.
            'approvals.status AS approval_status,'.
            'GROUP_CONCAT(worker.id) AS worker_ids,'.
            'GROUP_CONCAT(tasks.status) AS task_status,'.
            'MAX(tasks.updated_at) AS allocated_at,'.
            'allocator.id AS allocator_id,'.
            'request_files.name as request_file_name,'.
            'request_files.file_path as request_file_path,'.
            'request_mails.id as request_mail_id'
        );

        $form['sort_by'] = $form['sort_by'] === 'worker_ids_no_image' ? 'worker_ids': $form['sort_by'];
        $form['sort_by'] = $form['sort_by'] === 'allocator_id_no_image' ? 'allocator_id': $form['sort_by'];

        // sort
        if ($form['sort_by']) {
            $query = $query->orderBy($form['sort_by'], $form['descending']);
        }

        $query = $query->orderBy('request_works.id', 'asc');

        $list = $query->paginate($form['rows_per_page'], ['*'], 'page', (int) $form['page']);

        // 全ユーザ情報を保持
        $candidates = User::select(
            'users.id',
            'users.name',
            'users.user_image_path'
        )->get();

        return response()->json([
            'list' => $list,             // 割振一覧
            'candidates' => $candidates, // ユーザ情報
            'all_request_work_ids' => $all_request_work_ids //全件作業ID
        ]);
    }

    public function edit(Request $req)
    {
        $user = \Auth::user();

        $request_work_id = $req->input('request_work_id');

        $is_education = true;
        // 依頼情報を取得
        $request = RequestModel::getRelatedData($request_work_id, $user->id, $is_education);
        $request_work = RequestWork::find($request_work_id);

        // 依頼メール
        $request_mail = $request_work->requestMails()->select('id')->first();
        if (!is_null($request_mail)) {
            $request_mail_template_info = $this->tryCreateMailTemplateData($request_mail->id);
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
            // ラベルデータ
            // ※ 暫定的に必要な箇所に絞って取得するが、複数箇所にラベルが必要になれば依頼ファイルに限らず条件無しで全件取得、状況に応じてViewcomposerの使用も検討する。
            $label_ids = [];
            foreach ($column_configs as $column_config) {
                $label_ids[] = $column_config->label_id;
            }
            $label_data = Label::getLangKeySetByIds($label_ids);
        }

        $before_items = \DB::table('item_configs')
            ->select(
                'item_configs.*',
                'steps.url as step_url'
            )
            ->join('steps', 'item_configs.step_id', '=', 'steps.id')
            ->where('steps.id', $request->before_step_id)
            ->where('item_configs.is_deleted', \Config::get('const.FLG.INACTIVE'))// 削除していない
            ->orderBy('item_configs.sort', 'asc')
            ->get();

        // 自分が管理する業務の担当候補者を取得
        // $candidates = User::getCandidatesByAdminUser($user->id);

        // 全ユーザ情報を保持（途中で担当者から外れたケースにも対応するため）
        $candidates = User::get();

        $is_education = true;
        // 作業担当者リストを取得
        $operators = User::getOperatorsByRequestWorkIds(
            [$request_work_id],
            $request->business_id,
            $request->step_id,
            $is_education
        );

        // 業務管理者か判断
        $request->is_business_admin = true;

        return response()->json([
            'request' => $request,
            'candidates' => $candidates,
            'operators' => $operators,
            'before_items' => $before_items,
            'started_at' => Carbon::now(),
            'label_data' => $label_data,
        ]);
    }

    public function store(Request $req)
    {
        $user = \Auth::user();

        $request_id = $req->input('request_id');
        $request_work_id = $req->input('request_work_id');
        // 変更後の割振り状況（教育作業の表示非常時）
        $after_user_ids = explode(',', $req->input('after_user_ids'));
        $send_allocations = json_decode($req->input('sendAllocations'), true);

        // 割振り作業を開始日時
        $started_at = $req->input('started_at');

        // 排他チェック
        // ほかのユーザにより更新されているデータを取得 => あればエラー
        $tasks = Task::where('request_work_id', $request_work_id)
            ->where('updated_at', '>=', $started_at)
            ->where('is_education', '=', \Config::get('const.FLG.ACTIVE'))
            ->count();

        if ($tasks) {
            return response()->json([
                'result' => 'warning',
                'request_work_id' => $request_work_id,
            ]);
        }

        $request_works = RequestWork::where('id', $request_work_id)
            ->where('updated_at', '>=', $started_at)
            ->count();

        if ($request_works) {
            return response()->json([
                'result' => 'warning',
                'request_work_id' => $request_work_id,
            ]);
        }
        // /排他チェック

        // DB登録
        \DB::beginTransaction();
        try {
            // 担当から外したユーザがいる場合は物理削除する
            Task::where('request_work_id', $request_work_id)
                ->whereNotIn('user_id', $after_user_ids)
                ->where('status', '<>', \Config::get('const.TASK_STATUS.DONE'))
                ->where('is_active', '=', \Config::get('const.FLG.ACTIVE'))
                ->where('is_education', '=', \Config::get('const.FLG.ACTIVE'))
                ->delete();

            // 教育タスクにはやり直し機能がないのでコメントアウト
            // 担当から外したユーザの前回作業がある場合は最新の1件を有効にする
            // $latest_inactive_tasks = Task::selectRaw('MAX(tasks.id)')
            //     ->where('tasks.request_work_id', $request_work_id)
            //     ->whereNotIn('user_id', $after_user_ids)
            //     ->where('tasks.is_active', \Config::get('const.FLG.INACTIVE'))
            //     ->where('is_education', '=', \Config::get('const.FLG.ACTIVE'))
            //     ->groupBy('tasks.user_id')
            //     ->get();

            // Task::whereIn('tasks.id', $latest_inactive_tasks)
            //     ->where('is_education', '=', \Config::get('const.FLG.ACTIVE'))
            //     ->update(['tasks.is_active' => \Config::get('const.FLG.ACTIVE')]);

            $insert_tasks = array();

            $current_time = Carbon::now();
            $current_time->addDay();

            foreach ($send_allocations as $send_allocation) {
                $task = Task::where('user_id', $send_allocation['user_id'])
                    ->where('is_education', \Config::get('const.FLG.ACTIVE'))
                    ->where('request_work_id', $request_work_id)
                    ->get()->toArray();

                Task::updateOrCreate(
                    [
                        'user_id' => $send_allocation['user_id'],
                        'is_education' => \Config::get('const.FLG.ACTIVE'),
                        'request_work_id' => $request_work_id,
                    ],
                    [
                        'request_work_id' => $request_work_id,
                        'user_id' => $send_allocation['user_id'],
                        'status' => array_key_exists('status', $task) ? $task['status'] : \Config::get('const.TASK_STATUS.NONE'),
                        'is_active' => \Config::get('const.FLG.ACTIVE'),
                        'created_user_id' => array_key_exists('created_user_id', $task) ? $task['created_user_id'] : $user->id,
                        'updated_user_id' => $user->id,
                        'deadline' => array_key_exists('deadline', $task) ? $task['deadline'] : $current_time,
                        'system_deadline' => array_key_exists('system_deadline', $task) ? $task['system_deadline'] : $current_time,
                        'is_display_educational' => $send_allocation['is_display_educational'],
                        'is_education' => \Config::get('const.FLG.ACTIVE')
                    ]
                );
            }

            // updated_atの更新
            $request_work = RequestWork::find($request_work_id);
            $request_work->touch();

            // ログ登録(割振)
            // 教育タスクでログを出すのかなど検討事項があるため一旦コメントアウト
            // $request_log_attributes = [
            //     'request_id' => $request_id,
            //     'type' => \Config::get('const.REQUEST_LOG_TYPE.ALLOCATION_COMPLETED'),
            //     'created_user_id' => $user->id,
            //     'updated_user_id' => $user->id,
            // ];
            // $this->storeRequestLog($request_log_attributes);

            \DB::commit();

            return response()->json([
                'result' => 'success',
                'request_work_id' => $request_work_id,
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
                'request_work_id' => $request_work_id,
            ]);
        }
    }

    public function canMultipleAllocations(Request $req)
    {

        try {
            $request_work_ids = explode(',', $req->input('request_work_ids'));
            $query = \DB::table('request_works')
                ->selectRaw(
                    'request_works.step_id AS step_id'
                )
                ->whereIn('request_works.id', $request_work_ids);

            $step_ids = $query->pluck('step_id')->toArray();

            $status_code = 200;
            $errors = array();
            // step_idの一意性チェック
            if (count(array_unique($step_ids)) > 1) {
                $status_code = 412;
                $errors['is_step_id_error'] = true;
            }

            if ($status_code === 200) {
                return response()->json([
                    'status' => $status_code,
                ]);
            } else {
                return response()->json([
                    'status' => $status_code,
                    'errors' => $errors
                ]);
            }
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }
}

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

class AllocationsController extends Controller
{
    use RequestLogStoreTrait;
    use RequestFileTrait;
    use RequestMailTrait;

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

        list($list, $all_request_work_ids) = RequestWork::getSearchList($user->id, $form);

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

        // 依頼情報を取得
        $request = RequestModel::getRelatedData($request_work_id, $user->id);
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
        }

        // ラベルデータ
        $label_data = Label::getLangKeySetAll();

        // 自分が管理する業務の担当候補者を取得
        // $candidates = User::getCandidatesByAdminUser($user->id);

        // 全ユーザ情報を保持（途中で担当者から外れたケースにも対応するため）
        $candidates = User::get();

        // 作業担当者リストを取得
        $operators = User::getOperatorsByRequestWorkIds(
            [$request_work_id],
            $request->business_id,
            $request->step_id
        );

        // 業務管理者か判断
        $request->is_business_admin = true;

        return response()->json([
            'request' => $request,
            'candidates' => $candidates,
            'operators' => $operators,
            'started_at' => Carbon::now(),
            'label_data' => $label_data,
        ]);
    }

    public function store(Request $req)
    {
        $user = \Auth::user();

        $request_id = $req->input('request_id');
        $request_work_id = $req->input('request_work_id');
        // 変更前の割振り状況
        $before_user_ids = explode(',', $req->input('before_user_ids'));
        // 変更後の割振り状況
        $after_user_ids = explode(',', $req->input('after_user_ids'));
        // 割振り作業を開始日時
        $started_at = $req->input('started_at');

        // 排他チェック
        // ほかのユーザにより更新されているデータを取得 => あればエラー
        $tasks = Task::where('request_work_id', $request_work_id)
            ->where('is_education', '=', \Config::get('const.FLG.INACTIVE'))
            ->where('updated_at', '>=', $started_at)
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
                ->where('is_education', '=', \Config::get('const.FLG.INACTIVE'))
                ->delete();

            // 担当から外したユーザの前回作業がある場合は最新の1件を有効にする
            $latest_inactive_tasks = Task::selectRaw('MAX(tasks.id)')
                ->where('tasks.request_work_id', $request_work_id)
                ->whereNotIn('user_id', $after_user_ids)
                ->where('tasks.is_active', \Config::get('const.FLG.INACTIVE'))
                ->where('is_education', '=', \Config::get('const.FLG.INACTIVE'))
                ->groupBy('tasks.user_id')
                ->get();

            Task::whereIn('tasks.id', $latest_inactive_tasks)
                ->where('is_education', '=', \Config::get('const.FLG.INACTIVE'))
                ->update(['tasks.is_active' => \Config::get('const.FLG.ACTIVE')]);

            $insert_tasks = array();
            $current_time  = Carbon::now();

            // 既に割り振られているユーザIDを除外
            $insert_user_ids = array_values(array_diff($after_user_ids, $before_user_ids));

            // 指定納期、システム納期
            $request_work_deadline = \DB::table('request_works')
                ->where('id', $request_work_id)
                ->value('deadline');

            foreach ($insert_user_ids as $user_id) {
                $insert_task = [
                    'request_work_id' => $request_work_id,
                    'user_id' => $user_id,
                    'status' => \Config::get('const.TASK_STATUS.NONE'),
                    'is_active' => \Config::get('const.FLG.ACTIVE'),
                    'created_at' => $current_time,
                    'created_user_id' => $user->id,
                    'updated_at' => $current_time,
                    'updated_user_id' => $user->id,
                    'deadline' => $request_work_deadline,
                    'system_deadline' => $request_work_deadline
                ];
                array_push($insert_tasks, $insert_task);
            }

            Task::insert($insert_tasks);

            // updated_atの更新
            $request_work = RequestWork::find($request_work_id);
            $request_work->touch();

            // ログ登録(割振)
            $request_log_attributes = [
                'request_id' => $request_id,
                'type' => \Config::get('const.REQUEST_LOG_TYPE.ALLOCATION_COMPLETED'),
                'request_work_id' => $request_work_id,
                'created_user_id' => $user->id,
                'updated_user_id' => $user->id,
            ];
            $this->storeRequestLog($request_log_attributes);

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

    public function canMultipleAllocations(Request $req)
    {

        try {
            $request_work_ids = explode(',', $req->input('request_work_ids'));
            $query = \DB::table('request_works')
                ->selectRaw(
                    'approvals.status AS approval_status,'.
                    'request_works.step_id AS step_id'
                )
                ->leftJoin('approvals', 'request_works.id', '=', 'approvals.request_work_id')
                ->whereIn('request_works.id', $request_work_ids);

            $approval_status = $query->pluck('approval_status')->toArray();
            $step_ids = $query->pluck('step_id')->toArray();

            $status_code = 200;
            $errors = array();
            // ステータスチェック
            if (in_array(\Config::get('const.APPROVAL_STATUS.DONE'), $approval_status)) {
                $status_code = 412;
                $errors['is_approval_status_error'] = true;
            }

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

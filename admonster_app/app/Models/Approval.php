<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use \App\Services\Traits\SearchFormTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'request_work_id',
        'status',
        'result_type',
        'create_user_id',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /* -------------------- relations ------------------------- */

    /**
     * [管理]承認一覧
     *
     * @param string $user_id ユーザID
     * @param array $form 検索条件
     * @param bool $count_flg 件数取得フラグ
     * @return \Illuminate\Database\Eloquent\Collection|int
     */
    public static function getList(string $user_id, array $form, bool $count_flg = false)
    {
        $query = \DB::table('approvals')
            ->selectRaw(
                'companies.name as company_name ,'.
                'businesses.name as business_name,'.
                'requests.id AS request_id,'.
                'requests.status AS request_status,'.
                'request_works.id as request_work_id,'.
                'request_works.name as request_work_name,'.
                'request_works.client_name as client_name,'.
                'request_works.from as `from`,'.
                'request_works.to as `to`,'.
                'request_works.deadline as deadline,'.
                'request_works.system_deadline as system_deadline,'.
                'request_works.created_at as created_at,'.
                'request_mails.subject AS subject,'.
                'request_mails.from AS request_mail_from,'.
                'request_mails.to AS request_mail_to,'.
                'LEFT(request_mails.body,'.\Config::get('const.REQUEST_MAIL_BODY_MAX_LENGTH_ON_LIST').') AS request_mail_body,'.
                '(SELECT COUNT(request_mail_attachments.id)'.
                    ' FROM request_mail_attachments'.
                    ' WHERE request_mail_attachments.request_mail_id = request_mails.id'.
                ') as mail_attachments_count,'.
                'steps.name AS step_name,'.
                'approval_configs.is_auto AS is_auto,'.
                'approval_configs.conditions AS approval_conditions,'.
                'approver.id as approver_id,'.
                'GROUP_CONCAT(worker.id) AS worker_ids,'.
                'GROUP_CONCAT(tasks.status) AS task_status,'.
                'approvals.created_at as approval_time,'.
                'approvals.result_type as result_type,'.
                'approvals.status as approval_status,'.
                'request_files.name as request_file_name,'.
                'request_files.file_path as request_file_path,'.
                'CASE WHEN GROUP_CONCAT(deliveries.id) IS NULL THEN '.\Config::get('const.DELIVERY_STATUS.NONE').
                ' ELSE MAX(deliveries.status)'.
                ' END AS delivery_status,'.
                'request_mails.id as request_mail_id'
            )
            ->join('request_works', 'approvals.request_work_id', '=', 'request_works.id')
            ->leftJoin('request_work_mails', 'request_works.id', '=', 'request_work_mails.request_work_id')
            ->leftJoin('request_mails', 'request_work_mails.request_mail_id', '=', 'request_mails.id')
            ->leftJoin('request_work_files', 'request_works.id', '=', 'request_work_files.request_work_id')
            ->leftJoin('request_files', 'request_work_files.request_file_id', '=', 'request_files.id')
            ->join('tasks', 'request_works.id', '=', 'tasks.request_work_id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->join('approval_configs', 'steps.id', '=', 'approval_configs.step_id')
            ->join('users as worker', 'tasks.user_id', '=', 'worker.id')
            ->leftJoin('users as approver', function ($join) {
                $join->on('approvals.updated_user_id', '=', 'approver.id')
                    ->where('approvals.status', \Config::get('const.APPROVAL_STATUS.DONE'));
            })
            ->leftJoin('approval_tasks', function ($join) {
                $join->on('approval_tasks.approval_id', '=', 'approvals.id');
            })
            ->leftJoin('deliveries', 'deliveries.approval_task_id', '=', 'approval_tasks.id')
            ->join('requests', 'request_works.request_id', '=', 'requests.id')
            ->join('businesses', 'requests.business_id', '=', 'businesses.id')
            ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->join('companies', 'businesses.company_id', '=', 'companies.id')
            ->where('tasks.is_active', '<>', \Config::get('const.FLG.INACTIVE'))
            ->where('tasks.is_education', '=', \Config::get('const.FLG.INACTIVE'))
            ->where('businesses_admin.user_id', $user_id);

        // 依頼作業ID
        if (is_array(array_get($form, 'request_work_ids')) && !empty($form['request_work_ids'])) {
            $query = $query->whereIn('request_works.id', $form['request_work_ids']);
        }

        // 業務名
        if (array_get($form, 'business_name')) {
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
        if (array_get($form, 'client_name')) {
            $query = $query->where('request_works.client_name', 'LIKE', '%'.$form['client_name'].'%');
        }
        // 作業者
        if (array_get($form, 'worker')) {
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
                        ->where('tasks.is_education', '=', \Config::get('const.FLG.INACTIVE'));

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
        // 承認者
        if (array_get($form, 'approver')) {
            $query = $query->where('approver.name', 'LIKE', '%'.$form['approver'].'%');
        }
        // 件名
        if (array_get($form, 'subject')) {
            $query = $query->where('request_works.name', 'LIKE', '%'.$form['subject'].'%');
        }
        // 作業名
        if (array_get($form, 'step_name')) {
            $query = $query->where('steps.name', 'LIKE', '%'.$form['step_name'].'%');
        }

        // 設定期限カラム
        $column = 'request_works.deadline';
        if (array_get($form, 'date_type') == \Config::get('const.DATE_TYPE.CREATED')) {
            // 発生日カラムに変更
            $column = 'request_works.created_at';
        }
        // 指定カラムで期限設定
        $from = array_get($form, 'from');
        $to = array_get($form, 'to');
        if ($from && $to) {
            $query->whereBetween($column, [$from, $to]);
        } elseif ($from && !$to) {
            $query->where($column, '>=', $from);
        } elseif (!$from && $to) {
            $query->where($column, '<=', $to);
        }

        // タスクステータス
        $done_request_works = \DB::table('request_works')
            ->selectRaw('request_works.id AS id')
            ->join('tasks', 'request_works.id', '=', 'tasks.request_work_id')
            ->whereIn('tasks.status', [\Config::get('const.TASK_STATUS.DONE')])
            ->where('tasks.is_education', '=', \Config::get('const.FLG.INACTIVE'))
            ->groupBy('request_works.id');

        $query = $query->whereIn('request_works.id', $done_request_works);

        // 除外対象
        $query = $query->where('companies.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('businesses.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('steps.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('requests.status', '<>', \Config::get('const.REQUEST_STATUS.EXCEPT'));
        $query = $query->where('request_works.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('request_works.is_active', \Config::get('const.FLG.ACTIVE'));

        // 承認ステータス
        if (count(array_get($form, 'approval_status', [])) > 0) {
            $approval_statuses = array_column($form['approval_status'], 'value');
            $query = $query->whereIn('approvals.status', $approval_statuses);
        }

        $query = $query->groupBy(
            'companies.name',
            'businesses.name',
            'request_works.id',
            'request_works.name',
            'request_works.client_name',
            'request_works.from',
            'request_works.to',
            'request_works.deadline',
            'request_works.system_deadline',
            'request_works.created_at',
            'request_mails.id',
            'request_files.id',
            'approver.id',
            'approver.name',
            'approver.user_image_path',
            'approvals.id',
            'approvals.status'
        );

        // 該当件数を抽出してcountで返却する
        if ($count_flg) {
            return \DB::table(\DB::raw("({$query->toSql()}) as count_table"))
                ->mergeBindings($query)
                ->count();
        }

        // sort
        $sort_by = array_get($form, 'sort_by');
        $sort_by = $sort_by === 'worker_ids_no_image' ? 'worker_ids': $sort_by;
        $sort_by = $sort_by === 'approver_id_no_image' ? 'approver_id': $sort_by;
        if ($sort_by) {
            $query->orderBy($sort_by, array_get($form, 'descending'));
        }

        $query = $query->orderBy('request_works.id', 'asc');

        $approvals = $query->paginate($form['rows_per_page'], ['*'], 'page', (int) $form['page']);

        return $approvals;
    }

    /**
     * 承認済の依頼作業IDリストのクエリを返す
     *
     * @return $this(App\Models\Approval)
     */
    public static function getRequestWorkIdsByApprovedQuery()
    {
        return self::select('request_work_id')
            ->where('result_type', \Config::get('const.RESULT_TYPE.SUCCESS'))
            ->groupBy('request_work_id');
    }

    public function requestWork()
    {
        return $this->belongsTo(RequestWork::class);
    }

    public function approvalTask()
    {
        return $this->hasMany(ApprovalTask::class);
    }
}

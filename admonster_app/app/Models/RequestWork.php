<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Request as RequestModel;

class RequestWork extends Model
{
    use \App\Services\Traits\SearchFormTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'request_id',
        'before_work_id',
        'step_id',
        'client_name',
        'from',
        'to',
        'deadline',
        'system_deadline',
        'remarks',
        'create_user_id',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    // 同じ依頼から発生した他のcodeを返す
    public static function getOtherCodesInSameRequest($request_id, $own_code)
    {
        $query = self::where('request_id', $request_id)
            ->where('is_active', \Config::get('const.FLG.ACTIVE'))
            ->whereNotNull('code')
            ->whereNotIn('code', [$own_code])
            ->groupBy('code');

        $other_related_codes = $query->pluck('code');

        return $other_related_codes;
    }

    /**
     * 割振一覧の検索結果リストを取得する
     *
     * @param string $user_id ユーザID
     * @param array $form 検索条件
     * @param bool $count_flg 件数取得フラグ
     * @return array|int [検索結果リスト, 全件IDリスト]|件数
     */
    public static function getSearchList(string $user_id, array $form = [], bool $count_flg = false)
    {
        // 各request_workに関連する最新のtask.idを取得
        $max_task_id_of_each_request_work = \DB::table('tasks')
            ->selectRaw('MAX(tasks.id) AS id')
            ->whereRaw('tasks.is_education = ?', [\Config::get('const.FLG.INACTIVE')])
            ->groupBy('tasks.request_work_id');

        // 各作業依頼ごとに割振を行ったユーザIDリストを取得
        $request_work_asigned_user = \DB::table('tasks')
            ->select('request_work_id', 'created_user_id')
            ->joinSub($max_task_id_of_each_request_work, 'tasks_ids', 'tasks_ids.id', 'tasks.id');

        // 各request_workの割振担当者を取得
        $allocator_sql = \DB::table('users')
            ->selectRaw(
                'users.id AS id,'.
                'users.`name` AS `name`,'.
                'users.user_image_path AS user_image_path,'.
                'tasks.request_work_id AS request_work_id'
            )
            ->joinSub($request_work_asigned_user, 'tasks', 'tasks.created_user_id', 'users.id');

        // 割振一覧を取得
        $query = \DB::table('request_works')
            ->leftJoin('tasks', function ($sub) {
                $sub->on('request_works.id', '=', 'tasks.request_work_id')
                    ->where('tasks.is_active', '<>', \Config::get('const.FLG.INACTIVE'))
                    ->where('tasks.is_education', '=', \Config::get('const.FLG.INACTIVE'));
            })
            ->leftJoin('request_work_mails', 'request_works.id', '=', 'request_work_mails.request_work_id')
            ->leftJoin('request_mails', 'request_work_mails.request_mail_id', '=', 'request_mails.id')
            ->leftJoin('request_work_files', 'request_works.id', '=', 'request_work_files.request_work_id')
            ->leftJoin('request_files', 'request_work_files.request_file_id', '=', 'request_files.id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->join('allocation_configs', 'steps.id', '=', 'allocation_configs.step_id')
            ->leftJoin('users as worker', 'tasks.user_id', '=', 'worker.id')
            ->leftJoinSub($allocator_sql, 'allocator', 'allocator.request_work_id', 'request_works.id')
            ->join('requests', 'request_works.request_id', '=', 'requests.id')
            ->leftJoin('approvals', 'request_works.id', '=', 'approvals.request_work_id')
            ->join('businesses', 'requests.business_id', '=', 'businesses.id')
            ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->join('companies', 'businesses.company_id', '=', 'companies.id')
            ->leftJoin('approval_tasks', function ($join) {
                $join->on('approval_tasks.approval_id', '=', 'approvals.id');
            })
            ->leftJoin('deliveries', 'deliveries.approval_task_id', '=', 'approval_tasks.id')
            ->where('businesses_admin.user_id', $user_id);

        // 依頼作業ID
        if (is_array(array_get($form, 'request_work_ids')) && !empty($form['request_work_ids'])) {
            $query->whereIn('request_works.id', $form['request_work_ids']);
        }
        // 業務名
        if (array_get($form, 'business_name')) {
            // プレフィックス判定
            $prefix = substr($form['business_name'], 0, 1);
            if (config('const.MASTER_ID_PREFIX.TABLE_COLUMN.'. $prefix)) {
                // ID検索
                $query = self::getMasterIdSearchQuery($query, $form['business_name']);
            } else {
                $query->where('businesses.name', 'LIKE', '%'.$form['business_name'].'%');
            }
        }
        // 依頼主
        if (array_get($form, 'client_name')) {
            $query->where('request_works.client_name', 'LIKE', '%'.$form['client_name'].'%');
        }
        // 担当者
        if (array_get($form, 'worker')) {
            // 1,全角スペースを半角スペースへ変換
            $worker_names = str_replace('　', ' ', $form['worker']);
            // 2,前後のスペースを削除
            $worker_names = trim($worker_names);
            // 3,連続する半角スペースを半角スペースひとつに変換
            $worker_names = preg_replace('/\s+/', ' ', $worker_names);
            // 4,半角スペースで分割
            $worker_names = explode(' ', $worker_names);

            $query->whereIn(
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
        // 割振担当者
        if (array_get($form, 'allocator')) {
            $query->where('allocator.name', 'LIKE', '%'.$form['allocator'].'%');
        }
        // 件名
        if (array_get($form, 'subject')) {
            $query->where('request_works.name', 'LIKE', '%'.$form['subject'].'%');
        }
        // 作業名
        if (array_get($form, 'step_name')) {
            $query->where('steps.name', 'LIKE', '%'.$form['step_name'].'%');
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
        if (is_array(array_get($form, 'status')) && !empty($form['status'])) {
            $status = array_column($form['status'], 'value');
            $query->where(function ($query) use ($status) {
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
        if (array_get($form, 'has_not_working')) {
            $query->havingRaw('GROUP_CONCAT(DISTINCT tasks.status) <> ?', [\Config::get('const.TASK_STATUS.DONE')]);
        }
        // 除外対象
        $query->where('companies.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query->where('businesses.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query->where('steps.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query->where('requests.status', '<>', \Config::get('const.REQUEST_STATUS.EXCEPT'));
        $query->where('request_works.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query->where('request_works.is_active', \Config::get('const.FLG.ACTIVE'));

        $query->groupBy(
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

        // 該当件数を抽出してcountで返却する
        if ($count_flg) {
            $query->select('request_works.id');
            return \DB::table(\DB::raw("({$query->toSql()}) as count_table"))
                ->mergeBindings($query)
                ->count();
        }

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
            'allocation_configs.is_auto AS is_auto,'.
            'allocation_configs.method AS allocation_method,'.
            'approvals.status AS approval_status,'.
            'GROUP_CONCAT(worker.id) AS worker_ids,'.
            'GROUP_CONCAT(tasks.status) AS task_status,'.
            'MAX(tasks.updated_at) AS allocated_at,'.
            'allocator.id AS allocator_id,'.
            'request_files.name as request_file_name,'.
            'request_files.file_path as request_file_path,'.
            'requests.status as request_status,'.
            'CASE WHEN GROUP_CONCAT(deliveries.id) IS NULL THEN '.\Config::get('const.DELIVERY_STATUS.NONE').
            ' ELSE MAX(deliveries.status)'.
            ' END AS delivery_status,'.
            'request_mails.id as request_mail_id'
        );

        // sort
        $sort_by = array_get($form, 'sort_by');
        $sort_by = $sort_by === 'worker_ids_no_image' ? 'worker_ids': $sort_by;
        $sort_by = $sort_by === 'allocator_id_no_image' ? 'allocator_id': $sort_by;
        if ($sort_by) {
            $query->orderBy($sort_by, array_get($form, 'descending'));
        }
        $query->orderBy('request_works.id', 'asc');

        return [$query->paginate($form['rows_per_page'], ['*'], 'page', (int) $form['page']), $all_request_work_ids];
    }

    public static function delivery()
    {
        return self::select('deliveries.*')
            ->join('approvals', 'request_works.id', '=', 'approvals.request_work_id')
            ->join('approval_tasks', 'approvals.id', '=', 'approval_tasks.approval_id')
            ->join('deliveries', 'approval_tasks.id', '=', 'deliveries.approval_task_id')
            ->whereNotNull('deliveries.id')
            ->first();
    }

    /* -------------------- relations ------------------------- */

    public function request()
    {
        return $this->belongsTo(RequestModel::class);
    }

    public function task()
    {
        return $this->hasMany(Task::class);
    }

    public function requestMails()
    {
        return $this->belongsToMany(RequestMail::class, 'request_work_mails')
            ->withTimestamps();
    }

    public function requestFiles()
    {
        return $this->belongsToMany(RequestFile::class, 'request_work_files')
            ->withPivot('content', 'row_no')
            ->withTimestamps();
    }

    public function approval()
    {
        return $this->hasOne(Approval::class);
    }

    public function sendMails()
    {
        return $this->hasMany(SendMail::class);
    }

    public function step()
    {
        return $this->belongsTo(Step::class);
    }

    public function requestWorkRelatedMails()
    {
        return $this->belongsToMany(
            RequestMail::class,
            'request_work_related_mails',
            'request_work_id',
            'request_mail_id'
        )->withPivot('is_open_to_client', 'is_open_to_work', 'from', 'created_user_id', 'updated_user_id');
    }
}

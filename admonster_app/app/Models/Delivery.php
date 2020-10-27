<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use \App\Services\Traits\SearchFormTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'approval_task_id',
        'content',
        'create_user_id',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * [管理]納品一覧
     *
     * @param string $user_id ユーザID
     * @param array $form 検索条件
     * @param bool $count_flg 件数取得フラグ
     * @return array|int [検索結果リスト, 全件IDリスト]|件数
     */
    public static function getSearchList(string $user_id, array $form, bool $count_flg = false)
    {
        $query = \DB::table('deliveries')
            ->join('approval_tasks', 'approval_tasks.id', '=', 'deliveries.approval_task_id')
            ->join('approvals', 'approvals.id', '=', 'approval_tasks.approval_id')
            ->join('request_works', 'approvals.request_work_id', '=', 'request_works.id')
            ->leftJoin('request_work_mails', 'request_works.id', '=', 'request_work_mails.request_work_id')
            ->leftJoin('request_mails', 'request_work_mails.request_mail_id', '=', 'request_mails.id')
            ->leftJoin('request_work_files', 'request_works.id', '=', 'request_work_files.request_work_id')
            ->leftJoin('request_files', 'request_work_files.request_file_id', '=', 'request_files.id')
            ->join('tasks', 'request_works.id', '=', 'tasks.request_work_id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->join('users AS worker', 'tasks.user_id', '=', 'worker.id')
            ->join('requests', 'request_works.request_id', '=', 'requests.id')
            ->join('businesses', 'requests.business_id', '=', 'businesses.id')
            ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->join('companies', 'businesses.company_id', '=', 'companies.id')
            ->leftJoin('users AS deliverer', 'deliveries.updated_user_id', '=', 'deliverer.id')
            ->leftJoin('users AS deliver_data_user', function ($join) {
                $join->on('deliver_data_user.id', '=', 'tasks.user_id')
                    ->on('approval_tasks.task_id', '=', 'tasks.id')
                    ->on('deliveries.approval_task_id', '=', 'approval_tasks.id');
            })
            ->where('tasks.is_active', '<>', \Config::get('const.FLG.INACTIVE'))
            ->where('tasks.is_education', \Config::get('const.FLG.INACTIVE'))
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
                    $sub->select('tasks.request_work_id AS request_work_id')
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
        // 納品担当者
        if (array_get($form, 'deliverer')) {
            $query->where('deliverer.name', 'LIKE', '%'.$form['deliverer'].'%');
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
        } elseif (array_get($form, 'date_type') == \Config::get('const.DATE_TYPE.SCHEDULED')) {
            // 発生日カラムに変更
            $column = 'deliveries.assign_delivery_at';
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

        // 納品ステータス
        $delivery_statuses = array_get($form, 'delivery_status', []);
        if ($delivery_statuses) {
            $query->whereIn('deliveries.status', array_column($delivery_statuses, 'value'));
        }

        // タスクステータス
        $done_request_works = \DB::table('request_works')
            ->selectRaw('request_works.id AS id')
            ->join('tasks', 'request_works.id', '=', 'tasks.request_work_id')
            ->whereIn('tasks.status', [\Config::get('const.TASK_STATUS.DONE')])
            ->where('tasks.is_education', \Config::get('const.FLG.INACTIVE'))
            ->groupBy('request_works.id');
        $query->whereIn('request_works.id', $done_request_works);

        // 除外対象
        $query->where('companies.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query->where('businesses.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query->where('steps.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query->where('requests.status', '<>', \Config::get('const.REQUEST_STATUS.EXCEPT'));
        $query->where('request_works.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query->where('request_works.is_active', \Config::get('const.FLG.ACTIVE'));

        $query->groupBy(
            'deliveries.id',
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
            'approvals.id',
            'approvals.result_type'
        );

        // 該当件数を抽出してcountで返却する
        if ($count_flg) {
            $query->select('deliveries.id');
            return \DB::table(\DB::raw("({$query->toSql()}) as count_table"))
                ->mergeBindings($query)
                ->count();
        }

        // 全件作業IDを取得する
        $query_copy = clone $query;
        $all_request_work_ids = $query_copy->whereNotNull('deliveries.id')->pluck('request_works.id');

        // 納品一覧用にselect句を指定
        $query->selectRaw(
            'companies.name AS company_name ,'.
            'businesses.name AS business_name,'.
            'businesses.id AS business_id,'.
            'requests.id AS request_id,'.
            'requests.status AS request_status,'.
            'request_works.id AS request_work_id,'.
            'request_works.name AS request_work_name,'.
            'request_works.client_name AS client_name,'.
            'request_works.from AS `from`,'.
            'request_works.to AS `to`,'.
            'request_works.deadline AS deadline,'.
            'request_works.system_deadline AS system_deadline,'.
            'request_works.created_at AS created_at,'.
            'request_mails.subject AS subject,'.
            'request_mails.from AS request_mail_from,'.
            'request_mails.to AS request_mail_to,'.
            'LEFT(request_mails.body,'.\Config::get('const.REQUEST_MAIL_BODY_MAX_LENGTH_ON_LIST').') AS request_mail_body,'.
            '(SELECT COUNT(request_mail_attachments.id)'.
                ' FROM request_mail_attachments'.
                ' WHERE request_mail_attachments.request_mail_id = request_mails.id'.
            ') AS mail_attachments_count,'.
            'steps.name AS step_name,'.
            'MAX(deliverer.id) AS deliverer_id,'.
            'GROUP_CONCAT(deliver_data_user.id) AS delivery_data_creator,'.
            'GROUP_CONCAT(DISTINCT worker.id) AS worker_ids,'.
            'GROUP_CONCAT(tasks.status) AS task_status,'.
            'MAX(deliveries.created_at) AS delivery_time,'.
            'MAX(deliveries.status) AS delivery_status,'.
            'deliveries.id AS delivery_id,'.
            'MAX(deliveries.is_assign_date) AS is_assign_date,'.
            'MAX(deliveries.assign_delivery_at) AS assign_delivery_at,'.
            'MAX(deliveries.updated_at) AS updated_at,'.
            'approvals.result_type AS result_type,'.
            'request_files.name as request_file_name,'.
            'request_files.file_path as request_file_path,'.
            'request_mails.id as request_mail_id'
        );

        // sort
        $sort_by = array_get($form, 'sort_by');
        $sort_by = $sort_by === 'worker_ids_no_image' ? 'worker_ids': $sort_by;
        $sort_by = $sort_by === 'delivery_data_creator_no_image' ? 'delivery_data_creator': $sort_by;
        $sort_by = $sort_by === 'deliverer_id_no_image' ? 'deliverer_id': $sort_by;
        if ($sort_by) {
            $query->orderBy($sort_by, array_get($form, 'descending'));
        }
        $query->orderBy('request_works.id', 'asc');

        return [$query->paginate(array_get($form, 'rows_per_page', 20), ['*'], 'page', (int) array_get($form, 'page', 1)), $all_request_work_ids];
    }

    public static function getByRequestWorkId(string $request_work_id)
    {
        $data = self::join('approval_tasks', 'approval_tasks.id', '=', 'deliveries.approval_task_id')
                ->join('approvals', 'approvals.id', '=', 'approval_tasks.approval_id')
                ->join('request_works', 'request_works.id', '=', 'approvals.request_work_id')
                ->join('steps', 'steps.id', '=', 'request_works.step_id')
                ->where('approvals.request_work_id', $request_work_id)
                ->select(
                    'deliveries.*',
                    \DB::raw('CASE WHEN deliveries.id IS NULL THEN '. \Config::get('const.DELIVERY_STATUS.NONE') .' ELSE deliveries.status END AS delivery_status'),
                    'request_works.step_id',
                    'steps.before_work_template',
                    'approvals.status'
                )
                ->first();

        return $data;
    }

    /* -------------------- relations ------------------------- */

    public function approvalTask()
    {
        return $this->belongsTo(ApprovalTask::class);
    }

    public function deliveryFiles()
    {
        return $this->hasMany(DeliveryFile::class);
    }
}

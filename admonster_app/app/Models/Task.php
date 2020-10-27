<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Task extends Model
{
    use \App\Services\Traits\SearchFormTrait;

    const STATUS_NONE = 0;

    const STATUS_ON = 1;

    const STATUS_DONE = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'request_work_id',
        'is_active',
        'user_id',
        'status',
        'is_verified',
        'message',
        'is_education',
        'is_display_educational',
        'deadline',
        'created_user_id',
        'updated_user_id',
        'deadline',
        'system_deadline',
        'is_display_educational',
        'is_education'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * user_idをスコープ
     */
    public function scopeUserId(Builder $query, string $user_id)
    {
        return $query->where('user_id', $user_id);
    }

    /**
     * statusをスコープ
     */
    public function scopeStatus(Builder $query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * 複数のstatusをスコープ
     */
    public function scopeStatuses(Builder $query, array $status)
    {
        return $query->whereIn('status', $status);
    }

    // 無効化されているかを返す
    public static function checkIsInactivatedById($id)
    {
        $is_inactivated = self::query()->where('id', $id)
            ->where('tasks.is_active', \Config::get('const.FLG.INACTIVE'))
            ->count();

        return $is_inactivated > 0 ? true : false;
    }

    /**
     * 作業一覧の検索結果リストを取得する
     *
     * @param string $user_id ユーザID
     * @param array $form 検索条件
     * @param bool $search_flg 検索フラグ
     * @param bool $count_flg 件数取得フラグ
     * @return \Illuminate\Database\Eloquent\Collection|int
     */
    public static function getRelatedDataSetList(string $user_id, array $form, bool $search_flg, bool $count_flg = false)
    {
        // 依頼作業IDと納品IDを関連づけるSQL
        $sub_query = Approval::select('approvals.request_work_id as request_work_id', 'deliveries.id as delivery_id')
            ->join('approval_tasks', 'approvals.id', 'approval_tasks.approval_id')
            ->join('deliveries', 'approval_tasks.id', 'deliveries.approval_task_id');

        $query = \DB::table('tasks')
            ->leftJoin('request_works', 'tasks.request_work_id', '=', 'request_works.id')
            ->leftJoinSub($sub_query, 'relation_request_work_id_and_delivery_id', 'relation_request_work_id_and_delivery_id.request_work_id', 'request_works.id')
            ->leftJoin('requests', 'request_works.request_id', '=', 'requests.id')
            ->leftJoin('steps', 'request_works.step_id', '=', 'steps.id')
            ->leftJoin('businesses', 'requests.business_id', '=', 'businesses.id')
            ->leftJoin('companies', 'businesses.company_id', '=', 'companies.id')
            ->select(
                'tasks.*',
                'businesses.name as business_name',
                'steps.id as step_id',
                'steps.name as step_name',
                'steps.url as step_url',
                'steps.description as step_description',
                'request_works.request_id as request_id',
                'request_works.name as request_work_name',
                'request_works.client_name as client_name',
                'relation_request_work_id_and_delivery_id.delivery_id as delivery_id'
            )
            ->where('user_id', $user_id);

        /*
        * 検索
        */
        if (!$search_flg) {
            // デフォルト
            $query = $query->whereIn(
                'tasks.status',
                [\Config::get('const.TASK_STATUS.NONE'), \Config::get('const.TASK_STATUS.ON')]
            );
        } else {
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
            // 作業名
            if (array_get($form, 'step_name')) {
                $query = $query->where('steps.name', 'LIKE', '%'.$form['step_name'].'%');
            }

            // 設定期限カラム
            $column = 'tasks.deadline';
            if (array_get($form, 'date_type') == \Config::get('const.DATE_TYPE.CREATED')) {
                // 発生日カラムに変更
                $column = 'tasks.created_at';
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

            // 優先度 未検証 > ステータス
            if (array_get($form, 'unverified')) {// 未検証
                $query = $query->where('tasks.is_active', \Config::get('const.FLG.ACTIVE'))// 有効データ
                    ->where('tasks.is_verified', \Config::get('const.FLG.INACTIVE'))// 未検証
                    ->where('tasks.status', \Config::get('const.TASK_STATUS.DONE'))// 処理済み
                    ->whereNotNull('relation_request_work_id_and_delivery_id.delivery_id');// 納品データあり
            } elseif (is_array(array_get($form, 'status'))) {// ステータス
                $query = $query->Where(function ($q) use ($form) {
                    if (in_array(\Config::get('const.TASK_STATUS.NONE'), $form['status'])) {
                        $q->orWhere('tasks.status', \Config::get('const.TASK_STATUS.NONE'))
                            ->Where('tasks.is_active', \Config::get('const.FLG.ACTIVE'));
                    }
                    if (in_array(\Config::get('const.TASK_STATUS.ON'), $form['status'])) {
                        $q->orWhere('tasks.status', \Config::get('const.TASK_STATUS.ON'))
                            ->Where('tasks.is_active', \Config::get('const.FLG.ACTIVE'));
                    }
                    if (in_array(\Config::get('const.TASK_STATUS.DONE'), $form['status'])) {
                        $q->orWhere('tasks.status', \Config::get('const.TASK_STATUS.DONE'))
                            ->orWhere('tasks.is_active', \Config::get('const.FLG.INACTIVE'));
                    }
                });
            }
        }

        // 該当件数を抽出してcountで返却する
        if ($count_flg) {
            return \DB::table(\DB::raw("({$query->toSql()}) as count_table"))
                ->mergeBindings($query)
                ->count();
        }

        // sort
        if (array_get($form, 'sort_by')) {
            $descending = array_get($form, 'descending');
            // 特殊なソート
            if ($form['sort_by'] === 'is_verified') {
                // orderBy('request_works.id', 'asc');はdelivery_idのソートで無効になる
                /*
                    タスク一覧画面の項目「検証」内の表示は複数パターンあり、表示する条件が複雑。
                    そのため、is_verifiedのみでは各パターンがきれいに分かれて表示されないため、Order byを複数指定している。
                */
                $query = $query->orderBy('is_active', $descending)
                    ->orderBy('is_verified', $descending)
                    ->orderBy('status', $descending)
                    ->orderBy('delivery_id', $descending);
            } else {
                // 標準ソート
                $query = $query->orderBy($form['sort_by'], $descending);
            }
        }
        $query = $query->orderBy('request_works.id', 'asc');

        $tasks = $query->paginate($form['rows_per_page'], ['*'], 'page', (int) $form['page']);

        return $tasks;
    }

    public static function isDone(string $id)
    {
        $status = self::query()->where('id', $id)->pluck('status')->first();

        return ($status == \Config::get('const.TASK_STATUS.DONE')) ? true : false;
    }

    // [管理]作業一覧
    public static function getList(string $user_id, array $form, bool $is_education = false)
    {
        $query = \DB::table('tasks')
            ->select(
                'companies.name as company_name',
                'businesses.name as business_name',
                'requests.id as request_id',
                'request_works.id as request_work_id',
                'request_works.name as request_work_name',
                'request_works.client_name as client_name',
                'request_works.from as from',
                'request_works.to as to',
                'request_works.deadline as deadline',
                'request_works.system_deadline as system_deadline',
                'request_works.created_at as created_at',
                'request_mails.subject as subject',
                'request_mails.from as request_mail_from',
                'request_mails.to as request_mail_to',
                \DB::raw('left(request_mails.body,'.\Config::get('const.REQUEST_MAIL_BODY_MAX_LENGTH_ON_LIST').') as request_mail_body'),
                \DB::raw('count(request_mail_attachments.id) as mail_attachments_count'),
                'request_files.name as request_file_name',
                'request_files.file_path as request_file_path',
                'request_mails.id as request_mail_id',
                'steps.id as step_id',
                'steps.name as step_name',
                'steps.url as step_url',
                'users.id as user_id',
                'users.name as user_name',
                'users.user_image_path as user_image_path',
                'tasks.id as task_id',
                'tasks.status as status',
                'tasks.message as task_message',
                'tasks.is_active as task_is_active',
                // 'tasks.updated_at as finished_at',
                // 'task_results.started_at as started_at',
                // 'task_results.finished_at as finished_at',
                'tasks.created_at as task_created_at',
                'approvals.status as approval_status',
                'approval_tasks.approval_result as approval_result'
            )
            ->join('request_works', 'tasks.request_work_id', '=', 'request_works.id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->join('users', 'tasks.user_id', 'users.id')
            ->join('requests', 'request_works.request_id', '=', 'requests.id')
            ->join('businesses', 'requests.business_id', '=', 'businesses.id')
            ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->join('companies', 'businesses.company_id', '=', 'companies.id')
            // ->leftJoin('task_results', 'tasks.id',  '=', 'task_results.task_id')
            ->leftJoin('approvals', 'request_works.id', '=', 'approvals.request_work_id')
            ->leftJoin('approval_tasks', function ($join) {
                $join->on('approvals.id', '=', 'approval_tasks.approval_id')
                    ->on('tasks.id', '=', 'approval_tasks.task_id');
            })
            ->leftJoin('request_work_mails', 'request_works.id', '=', 'request_work_mails.request_work_id')
            ->leftJoin('request_mails', 'request_work_mails.request_mail_id', '=', 'request_mails.id')
            ->leftJoin('request_mail_attachments', 'request_mails.id', '=', 'request_mail_attachments.request_mail_id')
            ->leftJoin('request_work_files', 'request_works.id', '=', 'request_work_files.request_work_id')
            ->leftJoin('request_files', 'request_work_files.request_file_id', '=', 'request_files.id')
            ->where('businesses_admin.user_id', $user_id)
            ->where('tasks.is_education', $is_education)
            ->groupBy(
                'businesses.id',
                'requests.id',
                'request_works.id',
                'steps.id',
                'tasks.id',
                'users.id',
                'approvals.id',
                'approval_tasks.id',
                'request_mails.id',
                'request_files.id'
            );

        // 依頼作業ID
        if (is_array($form['request_work_ids']) && !empty($form['request_work_ids'])) {
            $query = $query->whereIn('request_works.id', $form['request_work_ids']);
        }
        // 業務名
        if (isset($form['business_name']) && $form['business_name']) {
            $query = $query->where('businesses.name', 'LIKE', '%'.$form['business_name'].'%');
        }
        // 依頼主
        if (isset($form['client_name']) && $form['client_name']) {
            $query = $query->where('request_works.client_name', 'LIKE', '%'.$form['client_name'].'%');
        }
        // 作業者
        if (isset($form['worker']) && $form['worker']) {
            $query = $query->where('users.name', 'LIKE', '%'.$form['worker'].'%');
        }
        // 件名
        if (isset($form['subject']) && $form['subject']) {
            $query = $query->where('request_works.name', 'LIKE', '%'.$form['subject'].'%');
        }
        // 作業名
        if (isset($form['step_name']) && $form['step_name']) {
            $query = $query->where('steps.name', 'LIKE', '%'.$form['step_name'].'%');
        }
        if (isset($form['date_type']) && $form['date_type']) {
            if ($form['date_type'] == \Config::get('const.DATE_TYPE.CREATED')) {
                // タスク発生日
                if ($form['from'] && $form['to']) {
                    $query = $query->whereBetween('tasks.created_at', [$form['from'], $form['to']]);
                } elseif ($form['from'] && !$form['to']) {
                    $query = $query->where('tasks.created_at', '>=', $form['from']);
                } elseif (!$form['from'] && $form['to']) {
                    $query = $query->where('tasks.created_at', '<=', $form['to']);
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
        }
        // ステータス
        if (isset($form['status']) && count($form['status']) > 0) {
            $status_values = array_column($form['status'], 'value');
            $query = $query->Where(function ($q) use ($status_values) {
                if (in_array(\Config::get('const.TASK_STATUS.NONE'), $status_values)) {
                    $q->orWhere('tasks.status', \Config::get('const.TASK_STATUS.NONE'))
                        ->Where('tasks.is_active', \Config::get('const.FLG.ACTIVE'));
                }
                if (in_array(\Config::get('const.TASK_STATUS.ON'), $status_values)) {
                    $q->orWhere('tasks.status', \Config::get('const.TASK_STATUS.ON'))
                        ->Where('tasks.is_active', \Config::get('const.FLG.ACTIVE'));
                }
                if (in_array(\Config::get('const.TASK_STATUS.DONE'), $status_values)) {
                    $q->orWhere('tasks.status', \Config::get('const.TASK_STATUS.DONE'))
                        ->orWhere('tasks.is_active', \Config::get('const.FLG.INACTIVE'));
                }
            });
        } else {
            // ステータスによる絞り込み無し
        }

        // 承認ステータス
        if (isset($form['approval_status']) && count($form['approval_status']) > 0) {
            $approval_statuses = array_column($form['approval_status'], 'value');

            $query = $query->where(function ($q) use ($approval_statuses) {
                $q->orWhereIn('approvals.status', $approval_statuses);
                if (in_array(\Config::get('const.APPROVAL_STATUS.NONE'), $approval_statuses)) {
                    $q->orWhereNull('approvals.status');
                }
            });
        }

        // TODO: 除外された依頼は非表示（後で画面の検索条件に加える）
        $query = $query->where('companies.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('businesses.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('steps.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('requests.status', '<>', \Config::get('const.REQUEST_STATUS.EXCEPT'));
        $query = $query->where('requests.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('request_works.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('request_works.is_active', \Config::get('const.FLG.ACTIVE'));

        if (isset($form['sort_by']) && $form['sort_by'] === 'user_id_no_image') {
            $form['sort_by'] = 'user_id';
        }

        // sort
        if (isset($form['sort_by']) && $form['sort_by']) {
            $query = $query->orderBy($form['sort_by'], $form['descending']);
        }
        $query = $query->orderBy('request_works.id', 'asc');

        if (isset($form['rows_per_page']) && $form['rows_per_page']) {
            return $query->paginate($form['rows_per_page'], ['*'], 'page', (int) $form['page']);
        }

        return $query->get();
    }

    /**
     * 進捗一覧のデータを取得する
     *
     * @param string $user_id ユーザID
     * @param array $form 検索条件
     * @param bool $count_flg 件数取得フラグ
     * @return array|int [検索結果リスト, 全件IDリスト]|件数
     */
    public static function getWorkSearchList(string $user_id, array $form, bool $count_flg = false)
    {
        $query = \DB::table('tasks')
            ->join('request_works', 'tasks.request_work_id', '=', 'request_works.id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->join('users', 'tasks.user_id', 'users.id')
            ->join('requests', 'request_works.request_id', '=', 'requests.id')
            ->join('businesses', 'requests.business_id', '=', 'businesses.id')
            ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->join('companies', 'businesses.company_id', '=', 'companies.id')
            ->leftJoin('approvals', 'request_works.id', '=', 'approvals.request_work_id')
            ->leftJoin('approval_tasks', function ($join) {
                $join->on('approvals.id', '=', 'approval_tasks.approval_id')
                    ->on('tasks.id', '=', 'approval_tasks.task_id');
            })
            ->leftJoin('deliveries', 'deliveries.approval_task_id', '=', 'approval_tasks.id')
            ->leftJoin('request_work_mails', 'request_works.id', '=', 'request_work_mails.request_work_id')
            ->leftJoin('request_mails', 'request_work_mails.request_mail_id', '=', 'request_mails.id')
            ->leftJoin('request_mail_attachments', 'request_mails.id', '=', 'request_mail_attachments.request_mail_id')
            ->leftJoin('request_work_files', 'request_works.id', '=', 'request_work_files.request_work_id')
            ->leftJoin('request_files', 'request_work_files.request_file_id', '=', 'request_files.id')
            ->where('businesses_admin.user_id', $user_id)
            ->where('tasks.is_education', false)
            ->groupBy(
                'businesses.id',
                'requests.id',
                'request_works.id',
                'steps.id',
                'tasks.id',
                'users.id',
                'approvals.id',
                'approval_tasks.id',
                'request_mails.id',
                'request_files.id'
            );

        // 依頼作業ID
        if (!empty($form['request_work_ids']) && is_array($form['request_work_ids'])) {
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
            // プレフィックス判定
            $prefix = substr($form['worker'], 0, 1);
            if (config('const.MASTER_ID_PREFIX.TABLE_COLUMN.'. $prefix)) {
                // ID検索
                $query = self::getMasterIdSearchQuery($query, $form['worker']);
            } else {
                $query->where('users.name', 'LIKE', '%'.$form['worker'].'%');
            }
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
            $column = 'tasks.created_at';
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
        // ステータス
        if (isset($form['status']) && count($form['status']) > 0) {
            $status_values = array_column($form['status'], 'value');
            $query->Where(function ($q) use ($status_values) {
                if (in_array(\Config::get('const.TASK_STATUS.NONE'), $status_values)) {
                    $q->orWhere('tasks.status', \Config::get('const.TASK_STATUS.NONE'))
                        ->Where('tasks.is_active', \Config::get('const.FLG.ACTIVE'));
                }
                if (in_array(\Config::get('const.TASK_STATUS.ON'), $status_values)) {
                    $q->orWhere('tasks.status', \Config::get('const.TASK_STATUS.ON'))
                        ->Where('tasks.is_active', \Config::get('const.FLG.ACTIVE'));
                }
                if (in_array(\Config::get('const.TASK_STATUS.DONE'), $status_values)) {
                    $q->orWhere('tasks.status', \Config::get('const.TASK_STATUS.DONE'))
                        ->orWhere('tasks.is_active', \Config::get('const.FLG.INACTIVE'));
                }
            });
        }
        // 承認ステータス
        if (isset($form['approval_status']) && count($form['approval_status']) > 0) {
            $approval_statuses = array_column($form['approval_status'], 'value');
            $query->where(function ($q) use ($approval_statuses) {
                $q->orWhereIn('approvals.status', $approval_statuses);
                if (in_array(\Config::get('const.APPROVAL_STATUS.NONE'), $approval_statuses)) {
                    $q->orWhereNull('approvals.status');
                }
            });
        }
        // 不明処理のみ
        if (array_get($form, 'task_contact')) {
            $query->where('tasks.is_defective', \Config::get('const.FLG.ACTIVE'));
        }

        $query->where('companies.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query->where('businesses.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query->where('steps.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query->where('requests.status', '<>', \Config::get('const.REQUEST_STATUS.EXCEPT'));
        $query->where('requests.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query->where('request_works.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query->where('request_works.is_active', \Config::get('const.FLG.ACTIVE'));

        // 該当件数を抽出してcountで返却する
        if ($count_flg) {
            $query->select('tasks.id');
            return \DB::table(\DB::raw("({$query->toSql()}) as count_table"))
                ->mergeBindings($query)
                ->count();
        }

        // 全件作業IDを取得する
        $all_request_work_ids = $query->pluck('request_works.id');

        $query->selectRaw(
            'companies.name as company_name,'.
            'businesses.name as business_name,'.
            'requests.id as request_id,'.
            'requests.status as request_status,'.
            'request_works.id as request_work_id,'.
            'request_works.name as request_work_name,'.
            'request_works.client_name as client_name,'.
            'request_works.from as `from`,'.
            'request_works.to as `to`,'.
            'request_works.deadline as deadline,'.
            'request_works.system_deadline as system_deadline,'.
            'request_works.created_at as created_at,'.
            'request_mails.subject as subject,'.
            'request_mails.from as request_mail_from,'.
            'request_mails.to as request_mail_to,'.
            'LEFT(request_mails.body,'.\Config::get('const.REQUEST_MAIL_BODY_MAX_LENGTH_ON_LIST').') as request_mail_body,'.
            'COUNT(request_mail_attachments.id) as mail_attachments_count,'.
            'request_files.name as request_file_name,'.
            'request_files.file_path as request_file_path,'.
            'request_mails.id as request_mail_id,'.
            'steps.id as step_id,'.
            'steps.name as step_name,'.
            'steps.url as step_url,'.
            'users.id as user_id,'.
            'users.name as user_name,'.
            'users.user_image_path as user_image_path,'.
            'tasks.id as task_id,'.
            'tasks.status as status,'.
            'tasks.message as task_message,'.
            'tasks.is_active as task_is_active,'.
            'tasks.is_defective as task_is_defective,'.
            'tasks.created_at as task_created_at,'.
            'approvals.status as approval_status,'.
            'CASE WHEN GROUP_CONCAT(deliveries.id) IS NULL THEN '.\Config::get('const.DELIVERY_STATUS.NONE').
            ' ELSE MAX(deliveries.status)'.
            ' END as delivery_status,'.
            'approval_tasks.approval_result as approval_result'
        );

        $sort_by = array_get($form, 'sort_by');
        $sort_by = $sort_by === 'user_id_no_image' ? 'user_id' : $sort_by;
        if ($sort_by) {
            $query->orderBy($sort_by, array_get($form, 'descending'));
        }
        $query->orderBy('request_works.id', 'asc');

        return [$query->paginate(array_get($form, 'rows_per_page', 20), ['*'], 'page', (int) array_get($form, 'page', 1)), $all_request_work_ids];
    }
    /**
     * 承認済タスクリストのクエリを返す
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getTaskListByApprovedQuery()
    {
        return self::select('tasks.*')
            ->joinSub(Approval::getRequestWorkIdsByApprovedQuery(), 'approvals', 'approvals.request_work_id', '=', 'tasks.request_work_id');
    }

    /* -------------------- relations ------------------------- */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requestWork()
    {
        return $this->belongsTo(RequestWork::class);
    }

    public function taskResults()
    {
        return $this->hasMany(TaskResult::class);
    }

    public function taskComment()
    {
        return $this->hasOne(TaskComment::class);
    }
}

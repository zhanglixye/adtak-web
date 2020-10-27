<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Request extends Model
{
    use \App\Services\Traits\SearchFormTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'business_id',
        'client_name',
        'deadline',
        'system_deadline',
        'create_user_id',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public static function getRelatedDataSetList(string $user_id, array $form)
    {
        if (! $form['status'] && ! $form['completed']) {
            return [];
        }

        $sub_query_tasks = self::createQueryTasks();

        $sub = null;
        // 未割振
        if (in_array(\Config::get('const.PROCESS_TYPE.ALLOCATION').\Config::get('const.TASK_STATUS.ON'), $form['status'])) {
            $query = self::createQueryInAllocation($sub_query_tasks);

            $sub = $query;
        }
        // 未作業
        if (in_array(\Config::get('const.PROCESS_TYPE.ALLOCATION').\Config::get('const.TASK_STATUS.DONE'), $form['status'])
            or in_array(\Config::get('const.PROCESS_TYPE.WORK').\Config::get('const.TASK_STATUS.ON'), $form['status'])) {
            $query = self::createQueryInWork($sub_query_tasks);

            if ($sub) {
                $sub = $sub->unionAll($query);
            } else {
                $sub = $query;
            }
        }
        // 未承認
        if (in_array(\Config::get('const.PROCESS_TYPE.ALLOCATION').\Config::get('const.TASK_STATUS.DONE'), $form['status'])
            or in_array(\Config::get('const.PROCESS_TYPE.WORK').\Config::get('const.TASK_STATUS.DONE'), $form['status'])
            or in_array(\Config::get('const.PROCESS_TYPE.APPROVAL').\Config::get('const.TASK_STATUS.ON'), $form['status'])) {
            $query = self::createQueryInApproval($sub_query_tasks);

            if ($sub) {
                $sub = $sub->unionAll($query);
            } else {
                $sub = $query;
            }
        }
        // 未納品
        if (in_array(\Config::get('const.PROCESS_TYPE.ALLOCATION').\Config::get('const.TASK_STATUS.DONE'), $form['status'])
            or in_array(\Config::get('const.PROCESS_TYPE.WORK').\Config::get('const.TASK_STATUS.DONE'), $form['status'])
            or in_array(\Config::get('const.PROCESS_TYPE.APPROVAL').\Config::get('const.TASK_STATUS.DONE'), $form['status'])
            or in_array(\Config::get('const.PROCESS_TYPE.DELIVERY').\Config::get('const.TASK_STATUS.ON'), $form['status'])) {
            $query = self::createQueryInDelivery($sub_query_tasks);

            if ($sub) {
                $sub = $sub->unionAll($query);
            } else {
                $sub = $query;
            }
        }
        // 完了済
        if ($form['completed']) {
            $query = self::createQueryCompleted($sub_query_tasks);

            if ($sub) {
                $sub = $sub->unionAll($query);
            } else {
                $sub = $query;
            }
        }

        $query = DB::table('requests')
            ->select(
                // 一覧に表示する項目
                'requests.id as id',
                'requests.status as request_status',
                'requests.is_deleted as request_is_deleted',
                'businesses.name as business_name',
                'companies.name as company_name',
                'request_works.name as request_work_name',
                'steps.id as step_id',
                'steps.name as step_name',
                'steps.url as step_url',
                'base.process as process',
                'base.status as status',
                'request_works.is_active as request_work_is_active',
                'request_works.created_at as request_work_created_at',
                'request_works.deadline as deadline',
                // その他
                // TODO : WF概算修正業務を早期運用開始するための暫定対応として作業者が1人の前提のもと以下join①からのデータ取得文を追加するが必要無くなり次第join文①ともに削除。2019/03/11
                // join文①データここから
                'tasks.id as task_id',
                // join文①データここまで
                'request_works.id as request_work_id',
                'allocation_configs.parallel as parallel',
                DB::raw('(select group_concat(user_id) from tasks where request_work_id = request_works.id) as user_ids'),
                DB::raw('(select group_concat(user_id) from tasks where request_work_id = request_works.id and status = '.\Config::get('const.TASK_STATUS.DONE').') as completed_user_ids'),
                'base.allocate_user_ids as allocate_user_ids',
                'approvals.created_user_id as approval_user_id'
            )
            ->join('request_works', 'requests.id', '=', 'request_works.request_id')
            ->join(DB::raw('('. $sub->toSql() .') as base'), 'request_works.id', '=', 'base.request_work_id')
            ->join('businesses', 'requests.business_id', '=', 'businesses.id')
            ->join('companies', 'businesses.company_id', '=', 'companies.id')
            ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->join('allocation_configs', 'steps.id', '=', 'allocation_configs.step_id')
            // join文①ここから
            ->leftJoin('tasks', 'request_works.id', '=', 'tasks.request_work_id')
            // join文①ここまで
            ->leftJoin('request_work_files', 'request_works.id', '=', 'request_work_files.request_work_id')
            ->leftJoin('request_files', 'request_work_files.request_file_id', '=', 'request_files.id')
            ->leftJoin('request_work_mails', 'request_works.id', '=', 'request_work_mails.request_work_id')
            ->leftJoin('request_mails', 'request_work_mails.request_mail_id', '=', 'request_mails.id')
            ->leftJoin('approvals', 'request_works.id', '=', 'approvals.request_work_id')
            ->leftJoin('approval_tasks', 'approvals.id', '=', 'approval_tasks.approval_id')
            ->leftJoin('deliveries', 'approval_tasks.id', '=', 'deliveries.approval_task_id')
            ->where('businesses_admin.user_id', $user_id);

        /*
            検索条件
        */
        // 依頼作業ID
        if (isset($form['request_work_ids']) && $form['request_work_ids']) {
            $query = $query->whereIn('request_works.id', $form['request_work_ids']);
        }
        // 依頼作業名
        if ($form['request_work_name']) {
            $query = $query->where('request_works.name', 'LIKE', '%'.$form['request_work_name'].'%');
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
        // 作業名
        if ($form['step_name']) {
            $query = $query->where('steps.name', 'LIKE', '%'.$form['step_name'].'%');
        }
        // // 依頼主
        // if ($form['client_name']) {
        //     $query = $query->where('request_works.client_name', 'LIKE', '%'.$form['client_name'].'%');
        // }
        // 担当者名
        if ($form['operator_name']) {
            $query = $query->where('base.user_names', 'LIKE', '%'.$form['operator_name'].'%');
        }
        // 発生日 or 納品期限
        if ($form['date_type'] == \Config::get('const.DATE_TYPE.CREATED')) {
            if ($form['from'] && $form['to']) {
                $query = $query->whereBetween('request_works.created_at', [$form['from'], $form['to']]);
            } elseif ($form['from'] && !$form['to']) {
                $query = $query->where('request_works.created_at', '>=', $form['from']);
            } elseif (!$form['from'] && $form['to']) {
                $query = $query->where('request_works.created_at', '<=', $form['to']);
            }
        } elseif ($form['date_type'] == \Config::get('const.DATE_TYPE.DEADLINE')) {
            if ($form['from'] && $form['to']) {
                $query = $query->whereBetween('request_works.deadline', [$form['from'], $form['to']]);
            } elseif ($form['from'] && !$form['to']) {
                $query = $query->where('request_works.deadline', '>=', $form['from']);
            } elseif (!$form['from'] && $form['to']) {
                $query = $query->where('request_works.deadline', '<=', $form['to']);
            }
        }
        // 依頼ファイル名
        if ($form['request_file_name']) {
            $query = $query->where('request_files.name', 'LIKE', '%'.$form['request_file_name'].'%');
        }
        // 依頼メール件名
        if ($form['request_mail_subject']) {
            $query = $query->where('request_mails.subject', 'LIKE', '%'.$form['request_mail_subject'].'%');
        }
        // 依頼メール宛先（To, Cc, Bccのいずれか）
        if ($form['request_mail_to']) {
            $query = $query->where(function ($query) use ($form) {
                $query->where('request_mails.to', 'LIKE', '%'.$form['request_mail_to'].'%')
                    ->orWhere('request_mails.cc', 'LIKE', '%'.$form['request_mail_to'].'%')
                    ->orWhere('request_mails.bcc', 'LIKE', '%'.$form['request_mail_to'].'%');
            });
        }
        // 自分が割振/承認した案件
        if ($form['self']) {
            $query = $query->where(function ($query) use ($user_id) {
                $query->where(DB::raw('find_in_set('.$user_id.', base.allocate_user_ids)'))
                    ->orWhere('approvals.created_user_id', $user_id);
            });
        }
        // 無効
        if ($form['inactive']) {
            // 無効を含む
            $query = $query->whereIn('request_works.is_active', [\Config::get('const.FLG.INACTIVE'), \Config::get('const.FLG.ACTIVE')]);
        } else {
            // 無効を含まない
            $query = $query->where('request_works.is_active', \Config::get('const.FLG.ACTIVE'));
        }
        // 除外
        if ($form['excluded']) {
            // 除外のみ表示
            $query = $query->where('requests.status', \Config::get('const.REQUEST_STATUS.EXCEPT'));
            $query = $query->where('request_works.is_active', \Config::get('const.ACTIVE_FLG.INACTIVE'));
            // // 除外を含む
            // $query = $query->whereIn('request_works.is_deleted', [\Config::get('const.FLG.INACTIVE'), \Config::get('const.FLG.ACTIVE')]);
        // } else {
            // 除外を含まない
            // $query = $query->where('request_works.is_deleted', \Config::get('const.FLG.INACTIVE'));
        }

        // sort
        if ($form['sort_by']) {
            $query = $query->orderBy($form['sort_by'], $form['descending']);
        }
        $query = $query->orderBy('request_works.id', 'asc');

        $requests = $query->paginate($form['rows_per_page'], ['*'], 'page', (int) $form['page']);

        return $requests;
    }

    public static function getRelatedData(string $request_work_id, string $user_id, Bool $is_education = false)
    {
        $sub = self::getRequestProcesses($is_education);

        $query = DB::table('requests')
            ->select(
                // 一覧に表示する項目
                'requests.id as id',
                'requests.status as request_status',
                'requests.is_deleted as request_is_deleted',
                'businesses.id as business_id',
                'businesses.name as business_name',
                'companies.name as company_name',
                'request_works.name as request_work_name',
                'steps.id as step_id',
                'steps.name as step_name',
                'steps.url as step_url',
                'base.process as process',
                'base.status as status',
                'request_works.is_active as request_work_is_active',
                'request_works.created_at as request_work_created_at',
                'request_works.deadline as deadline',
                // 前作業納品データ
                'before_request_works.step_id as before_step_id',
                'before_deliveries.id as before_delivery_id',
                'before_deliveries.content as before_delivery_content',
                // その他
                'request_works.id as request_work_id',
                'allocation_configs.parallel as parallel',
                DB::raw('(select group_concat(user_id) from tasks where request_work_id = request_works.id and tasks.is_education = '.var_export($is_education, true).') as user_ids'),
                DB::raw('(select group_concat(user_id) from tasks where request_work_id = request_works.id and tasks.is_education = '.var_export($is_education, true).' and status = '.\Config::get('const.TASK_STATUS.DONE').') as completed_user_ids')
            )
            ->join('request_works as request_works', 'requests.id', '=', 'request_works.request_id')
            ->join(DB::raw('('. $sub->toSql() .') as base'), 'request_works.id', '=', 'base.request_work_id')
            ->join('businesses', 'requests.business_id', '=', 'businesses.id')
            ->join('companies', 'businesses.company_id', '=', 'companies.id')
            ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->join('allocation_configs', 'steps.id', '=', 'allocation_configs.step_id')
            // ->leftJoin('tasks', 'request_works.id', '=', 'tasks.request_work_id')
            ->leftJoin('request_work_mails', 'request_works.id', '=', 'request_work_mails.request_work_id')
            ->leftJoin('request_works as before_request_works', 'request_works.before_work_id', '=', 'before_request_works.id')
            ->leftJoin('approvals', 'request_works.id', '=', 'approvals.request_work_id')
            ->leftJoin('approvals as before_approvals', 'before_request_works.id', '=', 'before_approvals.request_work_id')
            ->leftJoin('approval_tasks as before_approval_tasks', 'before_approvals.id', '=', 'before_approval_tasks.approval_id')
            ->leftJoin('deliveries as before_deliveries', 'before_approval_tasks.id', '=', 'before_deliveries.approval_task_id')
            ->where('request_works.id', $request_work_id)
            ->where('businesses_admin.user_id', $user_id);

        $requests = $query->first();

        return $requests;
    }

    public static function getRelatedDataByIds(array $request_work_ids, string $user_id, Bool $is_education = false)
    {
        $sub = self::getRequestProcesses($is_education);

        $query = DB::table('requests')
            ->select(
                // 一覧に表示する項目
                'requests.id as id',
                'requests.status as request_status',
                'businesses.id as business_id',
                'businesses.name as business_name',
                'companies.name as company_name',
                'request_works.name as request_work_name',
                'request_works.is_active as request_work_is_active',
                'steps.id as step_id',
                'steps.name as step_name',
                'base.process as process',
                'base.status as status',
                'request_works.created_at as request_work_created_at',
                'request_works.deadline as deadline',
                // 依頼メール
                'request_mails.id as mail_id',
                'request_mails.from as mail_from',
                'request_mails.from as mail_to',
                'request_mails.subject as mail_subject',
                'request_mails.content_type as mail_content_type',
                DB::raw('left(request_mails.body,'.\Config::get('const.REQUEST_MAIL_BODY_MAX_LENGTH_ON_LIST').') as mail_body'),
                'request_mails.recieved_at as mail_received_at',
                DB::raw('(select group_concat(concat(name, ":", file_path)) from request_mail_attachments where request_mail_id = request_mails.id) as mail_attachments'),
                // その他
                'request_works.id as request_work_id',
                'allocation_configs.parallel as parallel',
                DB::raw('(select group_concat(user_id) from tasks where request_work_id = request_works.id and tasks.is_education = '.var_export($is_education, true).' and tasks.is_active = 1) as user_ids'),
                DB::raw('(select group_concat(user_id) from tasks where request_work_id = request_works.id and tasks.is_education = '.var_export($is_education, true).' and tasks.is_active = 1 and status = '.\Config::get('const.TASK_STATUS.DONE').') as completed_user_ids'),
                DB::raw('(select group_concat(user_id) from tasks where request_work_id = request_works.id and tasks.is_education = '.var_export($is_education, true).' and tasks.is_active = 1 and status = '.\Config::get('const.TASK_STATUS.ON').') as on_task_user_ids')
            )
            ->join('request_works as request_works', 'requests.id', '=', 'request_works.request_id')
            ->join(DB::raw('('. $sub->toSql() .') as base'), 'request_works.id', '=', 'base.request_work_id')
            ->join('businesses', 'requests.business_id', '=', 'businesses.id')
            ->join('companies', 'businesses.company_id', '=', 'companies.id')
            ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->join('allocation_configs', 'steps.id', '=', 'allocation_configs.step_id')
            ->leftJoin('request_work_files', 'request_works.id', '=', 'request_work_files.request_work_id')
            ->leftJoin('request_files', 'request_work_files.request_file_id', '=', 'request_files.id')
            ->leftJoin('request_work_mails', 'request_works.id', '=', 'request_work_mails.request_work_id')
            ->leftJoin('request_mails', 'request_work_mails.request_mail_id', '=', 'request_mails.id')
            ->whereIn('request_works.id', $request_work_ids)
            ->where('businesses_admin.user_id', $user_id);

        $requests = $query->get();

        return $requests;
    }

    public static function getProcessAndStatusById(String $request_work_id)
    {
        $sub = self::getRequestProcesses();

        $query = DB::table('requests')
            ->select(
                'steps.id as step_id',
                'steps.name as step_name',
                'base.process as process',
                'base.status as status',
                'deliveries.id as delivery_id'
            )
            ->join('request_works as request_works', 'requests.id', '=', 'request_works.request_id')
            ->join(DB::raw('('. $sub->toSql() .') as base'), 'request_works.id', '=', 'base.request_work_id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->leftJoin('approvals', 'request_works.id', '=', 'approvals.request_work_id')
            ->leftJoin('approval_tasks', 'approvals.id', '=', 'approval_tasks.approval_id')
            ->leftJoin('deliveries', 'approval_tasks.id', '=', 'deliveries.approval_task_id')
            ->where('request_works.id', $request_work_id)
            ->where('request_works.is_active', \Config::get('const.REQUEST_WORK_ACTIVE_FLG.ACTIVE'));
            // ->where('request_works.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'));

        $requests = $query->first();

        return $requests;
    }

    public static function createQueryTasks()
    {
        $query = DB::table('request_works')
            ->select(
                'request_works.id as request_work_id',
                DB::raw('count(tasks.id) as task_count'),
                DB::raw('count(tasks.status = '.\Config::get('const.TASK_STATUS.DONE').' or null) as complete_count'),
                DB::raw('group_concat(tasks.created_user_id) as allocate_user_ids'),
                DB::raw('group_concat(users.name) as user_names')
            )
            ->leftJoin('tasks', 'request_works.id', '=', 'tasks.request_work_id')
            ->leftJoin('users', 'tasks.user_id', '=', 'users.id')
            ->groupBy('request_works.id');

        return $query;
    }

    // 未割振
    public static function createQueryInAllocation($sub)
    {
        $query = DB::table('requests')
            ->select(
                'request_works.id as request_work_id',
                DB::raw(\Config::get('const.PROCESS_TYPE.ALLOCATION').' as process'),
                DB::raw(\Config::get('const.TASK_STATUS.ON').' as status'),
                'tasks.allocate_user_ids as allocate_user_ids',
                'tasks.user_names as user_names'
            )
            ->join('request_works', 'requests.id', '=', 'request_works.request_id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->join('allocation_configs', 'steps.id', '=', 'allocation_configs.step_id')
            ->join(DB::raw('('.$sub->toSql().') as tasks'), function ($join) {
                $join->on('request_works.id', '=', 'tasks.request_work_id')
                    ->on('tasks.task_count', '<', 'allocation_configs.parallel');
            });

        return $query;
    }

    // 未作業
    public static function createQueryInWork($sub)
    {
        $query = DB::table('requests')
            ->select(
                'request_works.id as request_work_id',
                DB::raw(\Config::get('const.PROCESS_TYPE.WORK').' as process'),
                DB::raw(\Config::get('const.TASK_STATUS.ON').' as status'),
                'tasks.allocate_user_ids as allocate_user_ids',
                'tasks.user_names as user_names'
            )
            ->join('request_works', 'requests.id', '=', 'request_works.request_id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->join('allocation_configs', 'steps.id', '=', 'allocation_configs.step_id')
            ->leftJoin('approvals', 'request_works.id', '=', 'approvals.request_work_id')
            ->leftJoin('approval_tasks', 'approvals.id', '=', 'approval_tasks.approval_id')
            ->leftJoin('deliveries', 'approval_tasks.id', '=', 'deliveries.approval_task_id')
            ->join(DB::raw('('.$sub->toSql().') as tasks'), function ($join) {
                $join->on('request_works.id', '=', 'tasks.request_work_id')
                    ->on('allocation_configs.parallel', '<=', 'tasks.task_count')
                    ->on('tasks.complete_count', '<', 'tasks.task_count');
            })
            // 承認データ無し・納品データ無し
            ->whereNull('approvals.id')
            ->whereNull('deliveries.id');

        return $query;
    }

    // 未承認
    public static function createQueryInApproval($sub)
    {
        $query = DB::table('requests')
            ->select(
                'request_works.id as request_work_id',
                DB::raw(\Config::get('const.PROCESS_TYPE.APPROVAL').' as process'),
                DB::raw(\Config::get('const.TASK_STATUS.ON').' as status'),
                'tasks.allocate_user_ids as allocate_user_ids',
                'tasks.user_names as user_names'
            )
            ->join('request_works', 'requests.id', '=', 'request_works.request_id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->join('allocation_configs', 'steps.id', '=', 'allocation_configs.step_id')
            ->leftJoin('approvals', 'request_works.id', '=', 'approvals.request_work_id')
            ->leftJoin('approval_tasks', 'approvals.id', '=', 'approval_tasks.approval_id')
            ->leftJoin('deliveries', 'approval_tasks.id', '=', 'deliveries.approval_task_id')
            ->join(DB::raw('('.$sub->toSql().') as tasks'), function ($join) {
                $join->on('request_works.id', '=', 'tasks.request_work_id');
                    // 1人でも作業が完了していれば未承認とするので条件削除 2019/08/21
                    // ->on('allocation_configs.parallel', '<=', 'tasks.task_count')
                    // ->on('tasks.complete_count', '=', 'tasks.task_count');
            })
            // 承認データあり・ステータス承認済以外・納品データ無し
            ->whereNotNull('approvals.id')
            ->whereRaw('approvals.status <> '. \Config::get('const.APPROVAL_STATUS.DONE'))
            ->whereNull('deliveries.id');


        return $query;
    }

    // 未納品
    public static function createQueryInDelivery($sub)
    {
        $query = DB::table('requests')
            ->select(
                'request_works.id as request_work_id',
                DB::raw(\Config::get('const.PROCESS_TYPE.DELIVERY').' as process'),
                DB::raw(\Config::get('const.TASK_STATUS.ON').' as status'),
                'tasks.allocate_user_ids as allocate_user_ids',
                'tasks.user_names as user_names'
            )
            ->join('request_works', 'requests.id', '=', 'request_works.request_id')
            ->leftJoin('approvals', 'request_works.id', '=', 'approvals.request_work_id')
            ->leftJoin('approval_tasks', 'approvals.id', '=', 'approval_tasks.approval_id')
            ->leftJoin('deliveries', 'approval_tasks.id', '=', 'deliveries.approval_task_id')
            /* 完了件数とタスク件数が＝とは限らないためコメントアウト */
            // ->join('steps', 'request_works.step_id', '=', 'steps.id')
            // ->join('allocation_configs', 'steps.id', '=', 'allocation_configs.step_id')
            // ->leftJoin(DB::raw('('.$sub->toSql().') as tasks'), function($join) {
            //     $join->on('request_works.id', '=', 'tasks.request_work_id')
            //         ->on('tasks.task_count', '=', 'allocation_configs.parallel')
            //         ->on('tasks.task_count', '=', 'tasks.complete_count');
            // })
            ->leftJoin(DB::raw('('.$sub->toSql().') as tasks'), 'request_works.id', '=', 'tasks.request_work_id')
            // 承認データあり・ステータス承認済・納品データ無し
            ->whereNotNull('approvals.id')
            ->whereRaw('approvals.status = '.\Config::get('const.APPROVAL_STATUS.DONE'))
            ->whereNull('deliveries.id');

        return $query;
    }

    // 完了済案件
    public static function createQueryCompleted($sub)
    {
        $query = DB::table('requests')
            ->select(
                'request_works.id as request_work_id',
                DB::raw(\Config::get('const.PROCESS_TYPE.DELIVERY').' as process'),
                DB::raw(\Config::get('const.TASK_STATUS.DONE').' as status'),
                'tasks.allocate_user_ids as allocate_user_ids',
                'tasks.user_names as user_names'
            )
            ->join('request_works', 'requests.id', '=', 'request_works.request_id')
            ->leftJoin('approvals', 'request_works.id', '=', 'approvals.request_work_id')
            ->leftJoin('approval_tasks', 'approvals.id', '=', 'approval_tasks.approval_id')
            ->leftJoin('deliveries', 'approval_tasks.id', '=', 'deliveries.approval_task_id')
            /* 完了件数とタスク件数が＝とは限らないためコメントアウト */
            // ->join('steps', 'request_works.step_id', '=', 'steps.id')
            // ->join('allocation_configs', 'steps.id', '=', 'allocation_configs.step_id')
            // ->join(DB::raw('('.$sub->toSql().') as tasks'), function($join) {
            //     $join->on('request_works.id', '=', 'tasks.request_work_id')
            //         ->on('allocation_configs.parallel', '<=', 'tasks.task_count')
            //         ->on('tasks.task_count', '<=', 'tasks.complete_count');
            // })
            ->leftJoin(DB::raw('('.$sub->toSql().') as tasks'), 'request_works.id', '=', 'tasks.request_work_id')
            // 承認データあり・ステータス承認済・納品データあり
            ->whereNotNull('approvals.id')
            ->whereRaw('approvals.status = '.\Config::get('const.APPROVAL_STATUS.DONE'))
            ->whereNotNull('deliveries.id');

        return $query;
    }

    public static function getRequestProcesses(Bool $is_education = false)
    {
        // createQueryTasksの代替クエリ（依頼作業一覧画面以外はこちらの簡易クエリで対応するよう変更）
        $query = DB::table('request_works')
            ->selectRaw(
                'request_works.id as request_work_id,'.
                'CASE WHEN COUNT(tasks.id) = 0 THEN '.\Config::get('const.PROCESS_TYPE.ALLOCATION').
                '  WHEN COUNT(tasks.status = '.\Config::get('const.TASK_STATUS.DONE').' or NULL) = 0 THEN '
                    .\Config::get('const.PROCESS_TYPE.WORK').
                '  WHEN GROUP_CONCAT(DISTINCT approvals.status) IN ('
                    .\Config::get('const.APPROVAL_STATUS.NONE').', '.\Config::get('const.APPROVAL_STATUS.ON').') THEN '
                    .\Config::get('const.PROCESS_TYPE.APPROVAL').
                '  ELSE '.\Config::get('const.PROCESS_TYPE.DELIVERY').' END AS process,'.
                'CASE WHEN MAX(deliveries.status) = '.\Config::get('const.DELIVERY_STATUS.DONE').' THEN '.\Config::get('const.TASK_STATUS.DONE').
                '  ELSE '.\Config::get('const.TASK_STATUS.ON').' END AS status,'.
                // ===== 最新完了プロセス =====
                // 取込
                'CASE WHEN COUNT(tasks.id) = 0 THEN '.\Config::get('const.COMPLETED_PROCESS_TYPE.IMPORT').
                // 割振
                '  WHEN GROUP_CONCAT(DISTINCT tasks.status) IN ('
                    .\Config::get('const.TASK_STATUS.NONE').', '.\Config::get('const.TASK_STATUS.ON').') THEN '
                    .\Config::get('const.COMPLETED_PROCESS_TYPE.ALLOCATION').
                // タスク
                '  WHEN GROUP_CONCAT(DISTINCT tasks.status) NOT IN ('
                    .\Config::get('const.TASK_STATUS.NONE').', '.\Config::get('const.TASK_STATUS.ON').') AND GROUP_CONCAT(DISTINCT approvals.status) IN ('
                    .\Config::get('const.APPROVAL_STATUS.NONE').', '.\Config::get('const.APPROVAL_STATUS.ON').')THEN '
                    .\Config::get('const.COMPLETED_PROCESS_TYPE.WORK').
                // 承認
                '  WHEN GROUP_CONCAT(DISTINCT approvals.status) IN ('
                    .\Config::get('const.APPROVAL_STATUS.DONE').') AND COUNT(deliveries.id) = 0 THEN '
                    .\Config::get('const.COMPLETED_PROCESS_TYPE.APPROVAL').
                // 納品
                '  ELSE '.\Config::get('const.COMPLETED_PROCESS_TYPE.DELIVERY').' END AS completed_process'
            )
            ->leftJoin('tasks', function ($sub) use ($is_education) {
                $sub->on('request_works.id', '=', 'tasks.request_work_id');
                if ($is_education) {
                    $sub->whereRaw('tasks.is_education =' .\Config::get('const.FLG.ACTIVE'));
                } else {
                    $sub->whereRaw('tasks.is_education =' .\Config::get('const.FLG.INACTIVE'));
                }
            })
            ->leftJoin('approvals', 'request_works.id', '=', 'approvals.request_work_id')
            ->leftJoin('approval_tasks', 'approvals.id', '=', 'approval_tasks.approval_id')
            ->leftJoin('deliveries', 'approval_tasks.id', '=', 'deliveries.approval_task_id')
            ->groupBy('request_works.id');

        return $query;
    }

    /**
     * 依頼一覧の検索結果リストを取得する
     *
     * @param string $user_id ユーザID
     * @param array $form 検索条件
     * @param bool $count_flg 件数取得フラグ
     * @return \Illuminate\Database\Eloquent\Collection|int
     */
    public static function getRelatedRequestsList(string $user_id, array $form, bool $count_flg = false)
    {
        $query = DB::table('requests')
            ->select(
                // 一覧に表示する項目
                'companies.name as company_name',
                'businesses.name as business_name',
                'requests.id as request_id',
                'requests.name as request_name',
                'requests.client_name as client_name',
                'requests.created_at as created_at',
                'requests.deadline as deadline',
                'requests.status as status',
                'request_mails.subject as request_mail_subject',
                'request_mails.from as request_mail_from',
                'request_mails.to as request_mail_to',
                DB::raw('left(request_mails.body,'.\Config::get('const.REQUEST_MAIL_BODY_MAX_LENGTH_ON_LIST').') as request_mail_body'),
                'request_files.id as request_file_id',
                'request_files.name as request_file_name',
                'request_files.file_path as request_file_path',
                'request_mails.id as request_mail_id',
                DB::raw('count(request_mail_attachments.id) as mail_attachments_count')
            )
            ->join('request_works', 'requests.id', '=', 'request_works.request_id')
            ->join('businesses', 'requests.business_id', '=', 'businesses.id')
            ->join('companies', 'businesses.company_id', '=', 'companies.id')
            ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->leftJoin('request_work_files', 'request_works.id', '=', 'request_work_files.request_work_id')
            ->leftJoin('request_files', 'request_work_files.request_file_id', '=', 'request_files.id')
            ->leftJoin('request_work_mails', 'request_works.id', '=', 'request_work_mails.request_work_id')
            ->leftJoin('request_mails', 'request_work_mails.request_mail_id', '=', 'request_mails.id')
            ->leftJoin('request_mail_attachments', 'request_mails.id', '=', 'request_mail_attachments.request_mail_id')
            ->where('businesses_admin.user_id', $user_id)
            ->whereNull('request_works.before_work_id')
            ->where('companies.is_deleted', config('const.FLG.INACTIVE'))
            ->where('businesses.is_deleted', config('const.FLG.INACTIVE'))
            ->where('requests.is_deleted', config('const.FLG.INACTIVE'))
            ->where('request_works.is_deleted', config('const.FLG.INACTIVE'))
            ->groupBy(
                'businesses.id',
                'requests.id',
                'request_works.id',
                'request_mails.id',
                'request_files.id'
            );

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
            $query = $query->where('requests.client_name', 'LIKE', '%'.$form['client_name'].'%');
        }
        // 件名
        if (array_get($form, 'request_name')) {
            $query = $query->where('requests.name', 'LIKE', '%'.$form['request_name'].'%');
        }

        // ファイルID
        if (array_get($form, 'request_file_id')) {
            //全角数値の場合は半角数値へ変換
            $query = $query->where('request_files.id', mb_convert_kana($form['request_file_id'], "n"));
        }

        // status
        if (array_get($form, 'status')) {
            if ($form['status'] == config('const.REQUEST_STATUS.DOING')) {
                $query = $query->where('requests.is_deleted', '=', config('const.FLG.INACTIVE'));
                $query = $query->where('requests.status', config('const.REQUEST_STATUS.DOING'));
            } elseif ($form['status'] == config('const.REQUEST_STATUS.FINISH')) {
                $query = $query->where('requests.is_deleted', '=', config('const.FLG.INACTIVE'));
                $query = $query->where('requests.status', config('const.REQUEST_STATUS.FINISH'));
            } elseif ($form['status'] == config('const.REQUEST_STATUS.EXCEPT')) {
                $query = $query->where('requests.is_deleted', '=', config('const.FLG.INACTIVE'));
                $query = $query->where('requests.status', '=', config('const.REQUEST_STATUS.EXCEPT'));
            }
        }

        // 設定期限カラム
        $column = 'requests.deadline';
        if (array_get($form, 'date_type') == \Config::get('const.DATE_TYPE.CREATED')) {
            // 発生日カラムに変更
            $column = 'requests.created_at';
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

        // 該当件数を抽出してcountで返却する
        if ($count_flg) {
            return \DB::table(\DB::raw("({$query->toSql()}) as count_table"))
                ->mergeBindings($query)
                ->count();
        }

        //sort
        if (array_get($form, 'sort_by')) {
            $query = $query->orderBy($form['sort_by'], array_get($form, 'descending'));
        }

        $requests = $query->paginate(array_get($form, 'rows_per_page', 20), ['*'], 'page', (int) array_get($form, 'page', 1));

        return $requests;
    }

    /* -------------------- relations ------------------------- */

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function requestWork()
    {
        return $this->hasMany(RequestWork::class);
    }

    public function requestLogs()
    {
        return $this->hasMany(RequestLog::class);
    }

    public function relatedMails()
    {
        return $this->belongsToMany(
            RequestMail::class,
            'request_related_mails',
            'request_id',
            'request_mail_id'
        )->withPivot('is_open_to_client', 'is_open_to_work', 'from', 'created_user_id', 'updated_user_id');
    }

    public function requestAdditionalInfos()
    {
        return $this->hasMany(RequestAdditionalInfo::class);
    }

    public function orderDetails()
    {
        return $this->belongsToMany(OrderDetail::class, 'order_details_requests')
            ->withTimestamps();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Models\Request as RequestModel;

class Business extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'company_id',
        'description',
        'status',
        'create_user_id',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public static function getList(string $user_id, array $form)
    {
        $query = \DB::table('businesses')
            ->selectRaw(
                'businesses.id as business_id,'.
                'businesses.name as business_name,'.
                'companies.name as company_name,'.
                'COUNT(requests.id) as imported_count,'.
                'COUNT(requests.status = '.config('const.REQUEST_STATUS.EXCEPT').' or NULL) as excluded_count,'.
                'COUNT(requests.status = '.config('const.REQUEST_STATUS.FINISH').' or NULL) as completed_count,'.
                'COUNT(requests.status = '.config('const.REQUEST_STATUS.DOING').' or NULL) as wip_count'
            )
            ->join('companies', 'businesses.company_id', '=', 'companies.id')
            ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->leftJoin('requests', function ($join) use ($form) {
                $join->on('businesses.id', '=', 'requests.business_id')
                    ->where('requests.is_deleted', '=', config('const.FLG.INACTIVE'))
                    // 発生日 or 納品期限
                    ->when($form['date_type'] == config('const.DATE_TYPE.CREATED'), function ($q) use ($form) {
                        if ($form['search_from'] && $form['search_to']) {
                            return $q->whereBetween('requests.created_at', [$form['search_from'], $form['search_to']]);
                        } elseif ($form['search_from'] && !$form['search_to']) {
                            return $q->where('requests.created_at', '>=', $form['search_from']);
                        } elseif (!$form['search_from'] && $form['search_to']) {
                            return $q->where('requests.created_at', '<=', $form['search_to']);
                        }
                    })
                    ->when($form['date_type'] == config('const.DATE_TYPE.DEADLINE'), function ($q) use ($form) {
                        if ($form['search_from'] && $form['search_to']) {
                            return $q->whereBetween('requests.deadline', [$form['search_from'], $form['search_to']]);
                        } elseif ($form['search_from'] && !$form['search_to']) {
                            return $q->where('requests.deadline', '>=', $form['search_from']);
                        } elseif (!$form['search_from'] && $form['search_to']) {
                            return $q->where('requests.deadline', '<=', $form['search_to']);
                        }
                    });
            })
            ->where('businesses_admin.user_id', $user_id)
            ->where('companies.is_deleted', config('const.FLG.INACTIVE'))
            ->where('businesses.is_deleted', config('const.FLG.INACTIVE'))
            ->groupBy('businesses.id');

        // 企業名
        if ($form['company_name']) {
            $query = $query->where('companies.name', 'LIKE', '%'.$form['company_name'].'%');
        }

        $query = $query->orderBy('businesses.id', 'asc');

        $businesses = $query->get();

        return $businesses;
    }

    public static function searchBusinessStateList(string $user_id, array $form)
    {
        $sub = RequestModel::getRequestProcesses();

        $sub_query_business_flows = \DB::table('steps')
            ->select(
                'steps.id as step_id',
                'steps.name as step_name',
                'steps.end_criteria as end_criteria',
                'business_flows.business_id as business_id',
                \DB::raw('group_concat(distinct business_flows.next_step_id order by business_flows.next_step_id) as next_step_id_group')
            )
            ->join('business_flows', 'steps.id', '=', 'business_flows.step_id')
            ->groupBy('business_flows.business_id', 'steps.id');

        $query = DB::table('businesses')
            ->select(
                'business_flows.step_id as step_id',
                'business_flows.step_name as step_name',
                'business_flows.end_criteria as end_criteria',
                'business_flows.next_step_id_group as next_step_id_group',
                // count requests
                DB::raw('count(distinct requests.id) as request_all_count'),
                DB::raw('count(distinct case'
                    .' when requests.status = '.config('const.REQUEST_STATUS.EXCEPT')
                    .' then requests.id end) as request_excluded_count'),
                DB::raw('count(distinct case'
                    .' when requests.status = '.config('const.REQUEST_STATUS.DOING')
                    .' then requests.id end) as request_wip_count'),
                DB::raw('count(distinct case'
                    .' when requests.status = '.config('const.REQUEST_STATUS.FINISH')
                    .' then requests.id end) as request_completed_count'),
                // count request_works
                DB::raw('count(request_works.id) as imported_count'),
                DB::raw('count(request_works.is_active = '.config('const.ACTIVE_FLG.INACTIVE').' or null) as excluded_count'),
                DB::raw('count(request_works.is_active = '.config('const.ACTIVE_FLG.ACTIVE')
                    .' and base.process = '.config('const.PROCESS_TYPE.ALLOCATION')
                    .' and base.status = '.config('const.TASK_STATUS.ON').' or null) as allocation_count'),
                DB::raw('count(request_works.is_active = '.config('const.ACTIVE_FLG.ACTIVE')
                .' and base.process = '.config('const.PROCESS_TYPE.WORK')
                    .' and base.status = '.config('const.TASK_STATUS.ON').' or null) as work_count'),
                DB::raw('count(request_works.is_active = '.config('const.ACTIVE_FLG.ACTIVE')
                .' and base.process = '.config('const.PROCESS_TYPE.APPROVAL')
                    .' and base.status = '.config('const.TASK_STATUS.ON').' or null) as approval_count'),
                DB::raw('count(request_works.is_active = '.config('const.ACTIVE_FLG.ACTIVE')
                .' and base.process = '.config('const.PROCESS_TYPE.DELIVERY')
                    .' and base.status = '.config('const.TASK_STATUS.ON').' or null) as delivery_count'),
                DB::raw('count(request_works.is_active = '.config('const.ACTIVE_FLG.ACTIVE')
                .' and base.process in ('
                        .config('const.PROCESS_TYPE.ALLOCATION').','
                        .config('const.PROCESS_TYPE.WORK').','
                        .config('const.PROCESS_TYPE.APPROVAL').','
                        .config('const.PROCESS_TYPE.DELIVERY')
                    .') or null) as allocation_total'),
                DB::raw('count(request_works.is_active = '.config('const.ACTIVE_FLG.ACTIVE')
                .' and base.process in ('
                        .config('const.PROCESS_TYPE.WORK').','
                        .config('const.PROCESS_TYPE.APPROVAL').','
                        .config('const.PROCESS_TYPE.DELIVERY')
                    .') or null) as work_total'),
                DB::raw('count(request_works.is_active = '.config('const.ACTIVE_FLG.ACTIVE')
                .' and base.process in ('
                        .config('const.PROCESS_TYPE.APPROVAL').','
                        .config('const.PROCESS_TYPE.DELIVERY')
                    .') or null) as approval_total'),
                DB::raw('count(request_works.is_active = '.config('const.ACTIVE_FLG.ACTIVE')
                .' and base.process = '.config('const.PROCESS_TYPE.DELIVERY').' or null) as delivery_total')
            )
            ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->join(DB::raw('('. $sub_query_business_flows->toSql() .') as business_flows'), 'businesses.id', '=', 'business_flows.business_id')
            ->leftJoin('request_works', function ($join) use ($form) {
                $join->on('business_flows.step_id', '=', 'request_works.step_id')
                    // 無効を含まない場合は条件を追加
                    ->when(!$form['inactive'], function ($q) {
                        return $q->where('request_works.is_active', '=', config('const.FLG.ACTIVE'));
                    })
                    // 発生日 or 納品期限
                    // TODO: 定義の検討
                    ->when($form['date_type'] == config('const.DATE_TYPE.CREATED'), function ($q) use ($form) {
                        if ($form['search_from'] && $form['search_to']) {
                            return $q->whereBetween('request_works.created_at', [$form['search_from'], $form['search_to']]);
                        } elseif ($form['search_from'] && !$form['search_to']) {
                            return $q->where('request_works.created_at', '>=', $form['search_from']);
                        } elseif (!$form['search_from'] && $form['search_to']) {
                            return $q->where('request_works.created_at', '<=', $form['search_to']);
                        }
                    })
                    ->when($form['date_type'] == config('const.DATE_TYPE.DEADLINE'), function ($q) use ($form) {
                        if ($form['search_from'] && $form['search_to']) {
                            return $q->whereBetween('request_works.deadline', [$form['search_from'], $form['search_to']]);
                        } elseif ($form['search_from'] && !$form['search_to']) {
                            return $q->where('request_works.deadline', '>=', $form['search_from']);
                        } elseif (!$form['search_from'] && $form['search_to']) {
                            return $q->where('request_works.deadline', '<=', $form['search_to']);
                        }
                    });
            })
            ->leftJoin('requests', function ($join) {
                $join->on('request_works.request_id', '=', 'requests.id');
                    // 発生日 or 納品期限
                    // ->when($form['date_type'] == config('const.DATE_TYPE.CREATED'), function ($q) use ($form) {
                    //     if ($form['search_from'] && $form['search_to']) {
                    //         return $q->whereBetween('requests.created_at', [$form['search_from'], $form['search_to']]);
                    //     } elseif ($form['search_from'] && !$form['search_to']) {
                    //         return $q->where('requests.created_at', '>=', $form['search_from']);
                    //     } elseif (!$form['search_from'] && $form['search_to']) {
                    //         return $q->where('requests.created_at', '<=', $form['search_to']);
                    //     }
                    // })
                    // ->when($form['date_type'] == config('const.DATE_TYPE.DEADLINE'), function ($q) use ($form) {
                    //     if ($form['search_from'] && $form['search_to']) {
                    //         return $q->whereBetween('requests.deadline', [$form['search_from'], $form['search_to']]);
                    //     } elseif ($form['search_from'] && !$form['search_to']) {
                    //         return $q->where('requests.deadline', '>=', $form['search_from']);
                    //     } elseif (!$form['search_from'] && $form['search_to']) {
                    //         return $q->where('requests.deadline', '<=', $form['search_to']);
                    //     }
                    // });
            })
            ->leftJoin(DB::raw('('. $sub->toSql() .') as base'), 'request_works.id', '=', 'base.request_work_id')
            ->where('businesses_admin.user_id', $user_id)
            ->where('requests.is_deleted', '=', config('const.FLG.INACTIVE'))
            ->orWhereNull('requests.is_deleted')
            ->where('request_works.is_deleted', '=', config('const.FLG.INACTIVE'))
            ->orWhereNull('request_works.is_deleted')
            ->groupBy('businesses.id', 'business_flows.step_id')
            ->orderBy('business_flows.step_id', 'asc'); // 一旦は作業IDの昇順で表示することにする

        // 業務名
        if ($form['business_name']) {
            $query = $query->where('businesses.name', $form['business_name']);
        }

        $businesses = $query->get();

        return $businesses;
    }

    public static function searchBusinessStateConditions(string $user_id)
    {
        $query = DB::table('businesses')
            ->select(
                'businesses.id as business_id',
                'businesses.name as business_name'
            )
            ->leftJoin('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->where('businesses_admin.user_id', $user_id);

        $requests = $query->get();

        return $requests;
    }

    public static function getSearchConditions(string $user_id)
    {
        $query = DB::table('businesses')
            ->select(
                'businesses.id as business_id',
                'businesses.name as business_name',
                'companies.id as company_id',
                'companies.name as company_name',
                'users.id as user_id',
                'users.name as user_name'
                // 'request_files.id as file_id',
                // 'request_files.name as file_name',
                // 'request_mails.id as mail_id',
                // 'request_mails.from as mail_from',
                // 'request_mails.to as mail_to'
            )
            ->leftJoin('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->leftJoin('businesses_candidates', 'businesses.id', '=', 'businesses_candidates.business_id')
            ->leftJoin('companies', 'businesses.company_id', '=', 'companies.id')
            ->leftJoin('users', 'businesses_candidates.user_id', '=', 'users.id')
            // ->leftjoin('requests', 'businesses.id', '=', 'requests.business_id')
            // ->leftjoin('request_works', 'requests.id', '=', 'request_works.request_id')
            // ->leftjoin('request_work_files', 'request_works.id', '=', 'request_work_files.request_work_id')
            // ->leftjoin('request_files', 'request_work_files.file_id', '=', 'request_files.id')
            // ->leftjoin('request_work_mails', 'request_works.id', '=', 'request_work_mails.request_work_id')
            // ->leftjoin('request_mails', 'request_work_mails.mail_id', '=', 'request_mails.id')
            ->where('businesses_admin.user_id', $user_id);

        $requests = $query->get();

        return $requests;
    }

    public static function getDetailDataSetList(string $user_id, string $business_id)
    {
        $query = DB::table('businesses')
            ->select(
                'businesses.id as business_id',
                'businesses.name as business_name',
                'businesses.description as business_description',
                'companies.id as company_id',
                'companies.name as company_name',
                DB::raw('group_concat(DISTINCT businesses_admin.user_id) as admin_user_ids'),
                DB::raw('group_concat(DISTINCT businesses_candidates.user_id) as candidate_user_ids')
            )
            ->leftJoin('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->leftJoin('businesses_candidates', 'businesses.id', '=', 'businesses_candidates.business_id')
            ->Join('companies', 'businesses.company_id', '=', 'companies.id')
            ->where('businesses.id', $business_id)
            ->groupBy('business_id');

        $businesses = $query->first();

        // ログインユーザーが業務の管理者かどうかチェック
        // 処理を軽くするためサブクエリを使用せず取得後にチェック
        $admin_user_id_list = explode(',', $businesses->admin_user_ids);

        if (!in_array($user_id, $admin_user_id_list, true)) {
            // クエリビルダにて取得結果が0件の場合はnullが返却されるためそれに倣いnullを格納
            $businesses = null;
        }

        return $businesses;
    }

    public static function getNameByRequestId(string $request_id)
    {
        $query = self::select('businesses.name')
            ->join('requests', 'businesses.id', '=', 'requests.business_id')
            ->where('requests.id', $request_id);

        $business_name = $query->first()->name;

        return $business_name;
    }

    public static function getBusinessesList()
    {
        $query = DB::table('businesses')
            ->select(
                'businesses.id as business_id',
                'businesses.name as business_name',
                'businesses.description as business_description',
                'companies.name as company_name'
            )
            ->Join('companies', 'businesses.company_id', '=', 'companies.id');
        $businesses = $query->get();
        return $businesses;
    }
    public static function getBusinessById(int $business_id)
    {
        $query = DB::table('businesses')
            ->select(
                'businesses.id as business_id',
                'businesses.name as business_name',
                'businesses.description as business_description',
                'companies.name as company_name'
            )
            ->Join('companies', 'businesses.company_id', '=', 'companies.id')
            ->where('businesses.id', $business_id);
        $business_detail = $query->get();
        return $business_detail;
    }

    /* -------------------- relations ------------------------- */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function request()
    {
        return $this->hasMany(RequestModel::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'businesses_admin');
    }

    public function steps()
    {
        return $this->belongsToMany(Step::class, 'business_flows');
    }
}

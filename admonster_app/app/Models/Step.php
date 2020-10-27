<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use DB;

class Step extends Model
{
    const END_CRITERIA_ALL = 0;

    const END_CRITERIA_GROUP = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'step_type',
        'url',
        'description',
        'create_user_id',
        'update_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public static function getWorkInProcessCountList(array $form = [])
    {
        $query = \DB::table('steps')
            ->selectRaw(
                'businesses.id as business_id,'.
                'steps.id as step_id,'.
                'steps.name as step_name,'.
                'steps.end_criteria as end_criteria,'.
                'GROUP_CONCAT('.
                    'DISTINCT business_flows.next_step_id ORDER BY business_flows.next_step_id) as next_step_id_group,'.
                'GROUP_CONCAT(DISTINCT request_works.id) as request_work_ids,'.
                // ===== 依頼 =====
                // 取込
                'COUNT(DISTINCT requests.id) as request_all_count,'.
                'COUNT(DISTINCT CASE WHEN requests.status = '.\Config::get('const.REQUEST_STATUS.DOING').
                    ' THEN requests.id END) as request_wip_count,'.
                'COUNT(DISTINCT CASE WHEN requests.status = '.\Config::get('const.REQUEST_STATUS.FINISH').
                    ' THEN requests.id END) as request_completed_count,'.
                // 除外
                'COUNT(DISTINCT CASE WHEN requests.status = '.\Config::get('const.REQUEST_STATUS.EXCEPT').
                    ' THEN requests.id END) as request_excluded_count,'.
                // ===== 作業 =====
                // 取込
                'COUNT(DISTINCT request_works.id) as imported_count,'.
                // 除外
                'COUNT(DISTINCT CASE WHEN request_works.is_active = '.\Config::get('const.FLG.INACTIVE').
                    ' THEN request_works.id END) as excluded_count,'.
                // 割振
                'COUNT(DISTINCT CASE WHEN requests.status <> '.\Config::get('const.REQUEST_STATUS.EXCEPT').
                    ' AND tasks.status IS NULL THEN request_works.id END) as allocation_count,'.
                'COUNT(DISTINCT CASE WHEN requests.status <> '.\Config::get('const.REQUEST_STATUS.EXCEPT').
                    ' THEN request_works.id END) as allocation_total,'.
                // タスク
                'COUNT(DISTINCT CASE WHEN requests.status <> '.\Config::get('const.REQUEST_STATUS.EXCEPT').
                    ' AND tasks.status <> '.\Config::get('const.TASK_STATUS.DONE').
                    ' AND tasks.is_active = '.\Config::get('const.FLG.ACTIVE').
                    ' THEN tasks.id END) as work_count,'.
                'COUNT(DISTINCT CASE WHEN requests.status <> '.\Config::get('const.REQUEST_STATUS.EXCEPT').
                    ' THEN tasks.id END) as work_total,'.
                // 承認
                'COUNT(DISTINCT CASE WHEN requests.status <> '.\Config::get('const.REQUEST_STATUS.EXCEPT').
                    ' AND approvals.status <> '.\Config::get('const.APPROVAL_STATUS.DONE').
                    ' THEN approvals.id END) as approval_count,'.
                'COUNT(DISTINCT CASE WHEN requests.status <> '.\Config::get('const.REQUEST_STATUS.EXCEPT').
                    ' THEN approvals.id END) as approval_total,'.
                // 納品
                'COUNT(DISTINCT CASE WHEN requests.status <> '.\Config::get('const.REQUEST_STATUS.EXCEPT').
                    ' AND deliveries.id IS NULL'.
                    ' AND approvals.status = '.\Config::get('const.APPROVAL_STATUS.DONE').
                    ' THEN approvals.id END) as delivery_count,'.
                'COUNT(DISTINCT CASE WHEN requests.status <> '.\Config::get('const.REQUEST_STATUS.EXCEPT').
                    ' THEN deliveries.id END) as delivery_total'
            )
            ->join('business_flows', 'steps.id', '=', 'business_flows.step_id')
            ->join('businesses', 'business_flows.business_id', '=', 'businesses.id')
            ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->leftJoin('requests', function ($join) {
                $join->on('requests.business_id', '=', 'businesses.id')
                    ->where('requests.is_deleted', '=', config('const.FLG.INACTIVE'));
            })
            ->leftJoin('request_works', function ($join) {
                $join->on('requests.id', '=', 'request_works.request_id')
                    ->on('steps.id', '=', 'request_works.step_id')
                    ->where('request_works.is_deleted', '=', config('const.FLG.INACTIVE'));
            })
            ->leftJoin('tasks', function ($sub) {
                $sub->on('request_works.id', '=', 'tasks.request_work_id');
                    // ->where('tasks.is_active', '=', 1);
            })
            ->leftJoin('approvals', 'request_works.id', '=', 'approvals.request_work_id')
            ->leftJoin('approval_tasks', 'approvals.id', '=', 'approval_tasks.approval_id')
            ->leftJoin('deliveries', 'approval_tasks.id', '=', 'deliveries.approval_task_id')
            ->groupBy('businesses.id', 'steps.id');

        if ($form['request_id']) {
            $query = $query->where('requests.id', $form['request_id']);
        }

        return $query->get();
    }

    public static function getListByBusinessId(string $business_id)
    {
        $query = DB::table('steps')
            ->select(
                'steps.id as step_id',
                'steps.name as step_name',
                'steps.step_type as step_type',
                'steps.description as step_description',
                'steps.time_unit as time_unit',
                'steps.deadline_limit as deadline_limit',
                'steps.end_criteria as end_criteria',
                \DB::raw('group_concat(business_flows.business_id) as business_id'),
                \DB::raw('group_concat(business_flows.next_step_id) as next_step_id_group'),
                DB::raw('group_concat(DISTINCT works_users.user_id) as work_user_ids'),
                DB::raw('group_concat(DISTINCT educational_works_users.user_id) as educational_work_user_ids')
            )
            ->leftJoin('works_users', 'steps.id', '=', 'works_users.step_id')
            ->leftJoin('educational_works_users', 'steps.id', '=', 'educational_works_users.step_id')
            ->join('business_flows', 'steps.id', '=', 'business_flows.step_id')
            ->join('businesses_admin', 'business_flows.business_id', '=', 'businesses_admin.business_id')
            ->where('business_flows.business_id', $business_id)
            ->groupBy('steps.id');

        $steps = $query->get();

        return $steps;
    }

    public static function getFirstStepInBusinnessByBusinnessId(string $business_id)
    {
        $query = DB::table('steps')
            ->join('business_flows', 'steps.id', '=', 'business_flows.step_id')
            ->where('business_flows.business_id', $business_id)
            ->orderBy('steps.id', 'asc')
            ->orderBy('business_flows.seq_no', 'asc');

        $step = $query->first();

        return $step;
    }
    public static function getStepsByBusinessId(int $business_id)
    {
        $query = DB::table('steps')
            ->select('steps.id')
            ->join('business_flows', 'steps.id', '=', 'business_flows.step_id')
            ->distinct()
            ->where('business_flows.business_id', $business_id);
        $step_ids = $query->get();
        return $step_ids;
    }

    /**
     *  システム納品期限を計算する
     *
     * @param Carbon $datetime 軸となる日時
     * @param int $step_id 作業ID
     * @return Carbon $datetime システム納品期限
     */
    public static function calculateSystemDeadline(Carbon $datetime, int $step_id): Carbon
    {
        $step = self::where('is_deleted', \Config::get('const.FLG.INACTIVE'))->find($step_id);
        $time_unit = $step->time_unit;
        $deadline_limit = $step->deadline_limit;

        switch ($time_unit) {
            case \Config::get('const.TIME_UNIT.MINUTE'):
                $datetime->addMinute($deadline_limit);
                break;
            case \Config::get('const.TIME_UNIT.HOUR'):
                $datetime->addHour($deadline_limit);
                break;
            case \Config::get('const.TIME_UNIT.DAY'):
                $datetime->addDay($deadline_limit);
                break;
            default:
                $datetime->addDay();
        }
        return $datetime;
    }

    /* -------------------- relations ------------------------- */

    public function user()
    {
        return $this->belongsToMany(User::class);
    }

    public function taskResults()
    {
        return $this->hasMany(TaskResult::class);
    }

    public function requestWorks()
    {
        return $this->hasMany(RequestWork::class);
    }

    public function deliveryDestinations()
    {
        return $this->belongsToMany(DeliveryDestination::class);
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_flows');
    }

    public function createRequestWorkConfig()
    {
        return $this->hasOne(CreateRequestWorkConfig::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\CustomPasswordReset;
use Illuminate\Database\Eloquent\Builder;
use DB;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'nickname',
        'user_image_path',
        'sex',
        'birthday',
        'postal_code',
        'address',
        'tel',
        'email',
        'password',
        'password_changed_date',
        'remarks',
        'created_user_id',
        'updated_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomPasswordReset($token));
    }

    public function scopeId(Builder $query, string $id)
    {
        return $query->where('id', $id);
    }

    // 作業IDをもとに業務の担当者候補を取得（割振・作業状況も含む）
    public static function getOperatorsByRequestWorkIds(array $request_work_ids, int $business_id, int $step_id, bool $is_education = false)
    {
        // 該当する作業についての状況を[ユーザー×作業]単位で取得
        $task_group = DB::table('tasks')
            ->selectRaw(
                'tasks.user_id as user_id,'.
                'request_works.step_id as step_id,'.
                'COUNT(tasks.status = '.\Config::get('const.TASK_STATUS.NONE').' or NULL) as status_none_count,'.
                'COUNT(tasks.status = '.\Config::get('const.TASK_STATUS.ON').' or NULL) as status_on_count,'.
                'COUNT(tasks.status = '.\Config::get('const.TASK_STATUS.DONE').' or NULL) as status_done_count,'.
                'COUNT(approval_tasks.approval_result = '.\Config::get('const.APPROVAL_RESULT.OK').' or NULL) as ok,'.
                'COUNT(approval_tasks.approval_result = '.\Config::get('const.APPROVAL_RESULT.NG').' or NULL) as ng,'.
                'TRUNCATE(AVG(task_results.work_time), 2) as work_time_avg'
            )
            ->join('request_works', 'tasks.request_work_id', '=', 'request_works.id')
            ->leftJoin('approval_tasks', 'tasks.id', '=', 'approval_tasks.task_id')
            ->leftJoinSub(TaskResult::getFinishedTaskWorkTimeQuery(), 'task_results', 'tasks.id', 'task_results.task_id')
            ->where('tasks.is_active', \Config::get('const.FLG.ACTIVE'))
            ->where('request_works.is_active', \Config::get('const.FLG.ACTIVE'))
            ->where('request_works.is_deleted', \Config::get('const.FLG.INACTIVE'))
            ->groupBy('tasks.user_id', 'request_works.step_id');

        if ($is_education) {
            $task_group->whereRaw('tasks.is_education = ?', [\Config::get('const.FLG.ACTIVE')]);
        } else {
            $task_group->whereRaw('tasks.is_education = ?', [\Config::get('const.FLG.INACTIVE')]);
        }

        // 当該作業の関係者を取得
        // $work_candidates = DB::table('users')
        //     ->selectRaw(
        //         'DISTINCT '.
        //         'CASE WHEN businesses_candidates.business_id IS NOT NULL THEN businesses_candidates.business_id'.
        //             ' WHEN businesses_admin.business_id IS NOT NULL THEN businesses_admin.business_id'.
        //             ' ELSE NULL END as business_id,'.
        //         'works_users.step_id as step_id,'.
        //         'CASE WHEN works_users.user_id IS NOT NULL THEN works_users.user_id'.
        //             ' WHEN businesses_admin.user_id IS NOT NULL THEN businesses_admin.user_id'.
        //             ' ELSE NULL END as user_id'
        //     )
        //     ->leftJoin('businesses_candidates', function ($join) use ($business_id) {
        //         $join->on('users.id', '=', 'businesses_candidates.user_id')
        //             ->where('businesses_candidates.business_id', '=', $business_id);
        //     })
        //     ->leftJoin('works_users', function ($join) use ($step_id) {
        //         $join->on('businesses_candidates.user_id', '=', 'works_users.user_id')
        //             ->where('works_users.step_id', '=', $step_id);
        //     })
        //     ->leftJoin('businesses_admin', function ($join) use ($business_id) {
        //         $join->on('users.id', '=', 'businesses_admin.user_id')
        //             ->where('businesses_admin.business_id', '=', $business_id);
        //     });

        if ($is_education) {
            return User::getAllocationEducationOperators($request_work_ids, $task_group);
        } else {
            return User::getAllocationNormalOperators($request_work_ids, $task_group);
        }
    }

    // 通常割り振り
    public static function getAllocationNormalOperators(array $request_work_ids, object $task_group)
    {
        // TODO: 実績値は別テーブルで集計・管理する
        $query = DB::table('users')
            ->selectRaw(
                'users.id as user_id,'.
                'users.name as user_name,'.
                'users.user_image_path as user_image_path,'.
                '(task_group.status_none_count + task_group.status_on_count) as work_in_process_count,'.
                'task_group.status_done_count as completed_count,'.
                // TODO: 時間の算出（仮で[30min/件]として算出している
                '(task_group.status_none_count + task_group.status_on_count) * 30 AS estimated_time,'.
                'task_group.work_time_avg as average,'.
                'TRUNCATE(task_group.ok / (task_group.ok + task_group.ng) * 100, 1) AS percentage,'.
                'CASE WHEN FIND_IN_SET('.\Config::get('const.TASK_STATUS.DONE').', GROUP_CONCAT(tasks.status))'.
                        ' THEN '.\Config::get('const.TASK_STATUS.DONE').
                    ' WHEN FIND_IN_SET('.\Config::get('const.TASK_STATUS.ON').', GROUP_CONCAT(tasks.status))'.
                        ' THEN '.\Config::get('const.TASK_STATUS.ON').
                    ' WHEN FIND_IN_SET('.\Config::get('const.TASK_STATUS.NONE').', GROUP_CONCAT(tasks.status))'.
                        ' THEN '.\Config::get('const.TASK_STATUS.NONE').
                    ' ELSE NULL END as status' // 未割振ユーザはnull
            )
            // ->joinSub($work_candidates, 'work_candidates', function ($join) {
            //     $join->on('users.id', '=', 'work_candidates.user_id');
            // })
            ->join('businesses_candidates', 'users.id', '=', 'businesses_candidates.user_id')
            ->join('requests', 'businesses_candidates.business_id', '=', 'requests.business_id')
            ->join('request_works', 'requests.id', '=', 'request_works.request_id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->leftJoin('tasks', function ($join) {
                $join->on('request_works.id', '=', 'tasks.request_work_id')
                    ->on('users.id', '=', 'tasks.user_id')
                    ->where('tasks.is_active', '=', \Config::get('const.FLG.ACTIVE'))
                    ->whereRaw('tasks.is_education = ?', [\Config::get('const.FLG.INACTIVE')]);
            })
            ->leftJoinSub($task_group, 'task_group', function ($join) {
                $join->on('users.id', '=', 'task_group.user_id')
                    ->on('steps.id', '=', 'task_group.step_id');
            })
            ->whereIn('request_works.id', $request_work_ids)
            ->where('requests.is_deleted', \Config::get('const.FLG.INACTIVE'))
            ->where('request_works.is_deleted', \Config::get('const.FLG.INACTIVE'))
            ->groupBy('users.id', 'task_group.user_id', 'task_group.step_id');

            return $query->get();
    }

    // 教育割り振り
    public static function getAllocationEducationOperators(array $request_work_ids, object $task_group)
    {
        // TODO: 実績値は別テーブルで集計・管理する
        $query = DB::table('users')
            ->selectRaw(
                'users.id as user_id,'.
                'users.name as user_name,'.
                'users.user_image_path as user_image_path,'.
                '(task_group.status_none_count + task_group.status_on_count) as work_in_process_count,'.
                'task_group.status_done_count as completed_count,'.
                // TODO: 時間の算出（仮で[30min/件]として算出している
                '(task_group.status_none_count + task_group.status_on_count) * 30 AS estimated_time,'.
                'task_group.work_time_avg as average,'.
                // 'GROUP_CONCAT(tasks.id) as task_ids,'.
                'MIN(tasks.is_display_educational) as is_display_educational,'. // 0があったら0(非表示)
                'TRUNCATE(task_group.ok / (task_group.ok + task_group.ng) * 100, 1) AS percentage,'.
                'CASE WHEN FIND_IN_SET('.\Config::get('const.TASK_STATUS.DONE').', GROUP_CONCAT(tasks.status))'.
                        ' THEN '.\Config::get('const.TASK_STATUS.DONE').
                    ' WHEN FIND_IN_SET('.\Config::get('const.TASK_STATUS.ON').', GROUP_CONCAT(tasks.status))'.
                        ' THEN '.\Config::get('const.TASK_STATUS.ON').
                    ' WHEN FIND_IN_SET('.\Config::get('const.TASK_STATUS.NONE').', GROUP_CONCAT(tasks.status))'.
                        ' THEN '.\Config::get('const.TASK_STATUS.NONE').
                    ' ELSE NULL END as status' // 未割振ユーザはnull
            )
            ->join('educational_works_users', 'users.id', '=', 'educational_works_users.user_id')
            ->join('request_works', 'educational_works_users.step_id', '=', 'request_works.step_id')
            ->join('requests', 'request_works.request_id', '=', 'requests.id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->leftJoin('tasks', function ($join) {
                $join->on('request_works.id', '=', 'tasks.request_work_id')
                    ->on('users.id', '=', 'tasks.user_id')
                    ->where('tasks.is_active', '=', \Config::get('const.FLG.ACTIVE'))
                    ->whereRaw('tasks.is_education = ?', [\Config::get('const.FLG.ACTIVE')]);
            })
            ->leftJoinSub($task_group, 'task_group', function ($join) {
                $join->on('users.id', '=', 'task_group.user_id')
                    ->on('steps.id', '=', 'task_group.step_id');
            })
            ->whereIn('request_works.id', $request_work_ids)
            ->where('requests.is_deleted', \Config::get('const.FLG.INACTIVE'))
            ->where('request_works.is_deleted', \Config::get('const.FLG.INACTIVE'))
            ->groupBy('users.id', 'task_group.user_id', 'task_group.step_id');

            return $query->get();
    }

    public static function getCandidatesByAdminUser(string $user_id)
    {
        $query = DB::table('users')
            ->select(
                'users.id',
                'users.name',
                'users.user_image_path'
            )
            ->leftJoin('businesses_candidates', 'users.id', '=', 'businesses_candidates.user_id')
            ->leftJoin('businesses', 'businesses_candidates.business_id', '=', 'businesses.id')
            ->leftJoin('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->where('businesses_admin.user_id', $user_id)
            ->groupBy('users.id');

        $users = $query->get();

        return $users;
    }

    public static function getWithTaskCountInAdminBusinesses(int $business_admin_id, int $worker_id)
    {
        // ユーザー情報 + 管理者が管理中の業務の作業件数も取得
        $query = DB::table('users')
            ->select(
                'users.*',
                \DB::raw('count(tasks.id) as total_task_count'),
                \DB::raw('count(if(tasks.status = '.\Config::get('const.TASK_STATUS.NONE').', tasks.id, null)) as none_task_count'),
                \DB::raw('count(if(tasks.status = '.\Config::get('const.TASK_STATUS.ON').', tasks.id, null)) as on_task_count'),
                \DB::raw('count(if(tasks.status = '.\Config::get('const.TASK_STATUS.DONE').', tasks.id, null)) as completed_task_count'),
                \DB::raw('count(if(tasks.status = '.\Config::get('const.TASK_STATUS.NONE').', tasks.id, null)) + count(if(tasks.status = '.\Config::get('const.TASK_STATUS.ON').', tasks.id, null)) as uncompleted_task_count')
            )
            ->join('tasks', 'users.id', '=', 'tasks.user_id')
            ->join('request_works', 'tasks.request_work_id', '=', 'request_works.id')
            ->join('steps', 'request_works.step_id', '=', 'steps.id')
            ->join('requests', 'request_works.request_id', '=', 'requests.id')
            ->join('businesses', 'requests.business_id', '=', 'businesses.id')
            ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->join('companies', 'businesses.company_id', '=', 'companies.id')
            ->where('businesses_admin.user_id', $business_admin_id)
            ->where('users.id', $worker_id)
            ->groupBy('users.id');

        // TODO: '/works'との整合性を保つため、除外された依頼は非表示とするが、'/works'の検索条件が追加され次第要調整
        $query = $query->where('companies.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('businesses.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('steps.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('requests.status', '<>', \Config::get('const.REQUEST_STATUS.EXCEPT'));
        $query = $query->where('requests.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('request_works.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $query = $query->where('request_works.is_active', \Config::get('const.FLG.ACTIVE'));

        return $query->first();
    }

    public static function getAdminUsersByBusinessId(int $business_id)
    {
        $query = DB::table('users')
            ->select('users.*')
            ->join('businesses_admin', 'businesses_admin.user_id', '=', 'users.id')
            ->where('businesses_admin.business_id', $business_id)
            ->where('users.is_deleted', \Config::get('const.FLG.INACTIVE'))
            ->orderBy('users.id', 'asc');
        $admin_users = $query->get();
        return $admin_users;
    }

    public static function getOperatorsByBusinessId(int $business_id)
    {
        $query = DB::table('users')
            ->select('users.*')
            ->join('businesses_candidates', 'businesses_candidates.user_id', '=', 'users.id')
            ->where('businesses_candidates.business_id', $business_id)
            ->where('users.is_deleted', \Config::get('const.FLG.INACTIVE'))
            ->orderBy('users.id', 'asc');
        $operator_users = $query->get();
        return $operator_users;
    }

    public static function getCandidateUsersByuserIds(array $user_ids)
    {
        $query = DB::table('users')
            ->select('users.*')
            ->whereNotIn('users.id', $user_ids)
            ->where('users.is_deleted', \Config::get('const.FLG.INACTIVE'));
        $candidate_users = $query->get();
        return $candidate_users;
    }
    public static function getRelatedUserList(array $form)
    {
        $query = DB::table('users')
            ->select('users.*')
            ->where('users.is_deleted', \Config::get('const.FLG.INACTIVE'));
        // id
        if (array_get($form, 'id')) {
            $query = $query->where('users.id', $form['id']);
        }
        if (array_get($form, 'name')) {
            $query = $query->where('users.name', 'LIKE', '%'.$form['name'].'%');
        }
        if (array_get($form, 'email')) {
            $query = $query->where('users.email', 'LIKE', '%'.$form['email'].'%');
        }
        //sort
        if (array_get($form, 'sort_by')) {
            $query = $query->orderBy($form['sort_by'], array_get($form, 'descending'));
        }

        $users = $query->paginate(array_get($form, 'rows_per_page', 20), ['*'], 'page', (int) array_get($form, 'page', 1));
        return $users;
    }

    /* -------------------- relations ------------------------- */

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'businesses_admin');
    }

    public function businessesInCharge()  // 担当業務
    {
        return $this->belongsToMany(Business::class, 'businesses_candidates');
    }

    public function task()
    {
        return $this->hasMany(Task::class);
    }

    public function step()
    {
        return $this->belongsToMany(Step::class);
    }

    public function adminOrders()
    {
        return $this->belongsToMany(Order::class, 'orders_administrators');
    }

    public function sharerOrders()
    {
        return $this->belongsToMany(Order::class, 'orders_sharers');
    }
}

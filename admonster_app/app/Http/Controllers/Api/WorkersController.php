<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Step;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class WorkersController extends Controller
{
    public function show(Request $req)
    {
        $user = \Auth::user();
        $worker_id = $req->worker_id;
        $user_data = User::getWithTaskCountInAdminBusinesses($user->id, $worker_id);

        return response()->json([
            'user_data' => $user_data,
        ]);
    }

    public function performanceIndex(Request $req)
    {
        $user = \Auth::user();
        $worker_id = $req->worker_id;

        $query = \DB::table('steps')
            ->select(
                'steps.id as step_id',
                'steps.name as step_name',
                \DB::raw('group_concat(distinct businesses.name) as business_name'),
                \DB::raw('group_concat(distinct businesses.id) as business_id'),
                \DB::raw('count(if(tasks.status = '.\Config::get('const.TASK_STATUS.DONE').', tasks.id, null)) as completed_task_count'),
                \DB::raw('group_concat(distinct request_works.id) as request_work_ids')
            )
            ->join('request_works', 'steps.id', '=', 'request_works.step_id')
            ->join('tasks', 'request_works.id', '=', 'tasks.request_work_id')
            ->join('business_flows', 'steps.id', '=', 'business_flows.step_id')
            ->join('businesses', 'business_flows.business_id', '=', 'businesses.id')
            ->where('tasks.user_id', $worker_id)
            ->where('tasks.status', \Config::get('const.TASK_STATUS.DONE'))
            ->groupBy('steps.id');

        // 検索  ※暫定的に現在から1週間前の日のまでの実績に絞る
        $query = $query->where('tasks.updated_at', '>=', Carbon::now()->subWeek(1)->toDateString());

        $businesses = $query->get()->groupBy('business_name');

        $list = [];
        $tmp_arr = [];
        foreach ($businesses as $key => $steps) {
            $tmp_arr = [];
            $tmp_arr['business_name'] = $key;
            foreach ($steps as $step) {
                $tmp_arr['business_id'] = $step->business_id;
                $tmp_arr['steps'][] = $step;
            }
            $list[] = $tmp_arr;
        }

        $admin_business_ids = Business::join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->where('businesses_admin.user_id', $user->id)
            ->pluck('businesses.id');

        return response()->json([
            'list' => $list,
            'admin_business_ids' => $admin_business_ids,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Task;
use App\Models\User;
use App\Services\Traits\RequestMailTrait;

class WorksController extends Controller
{
    use RequestMailTrait;

    public function index(Request $request)
    {
        $user = \Auth::user();

        // 検索条件の取得
        $form = [
            'request_work_ids' => $request->input('request_work_ids'),
            'business_name' => $request->input('business_name'),
            'date_type' => $request->input('date_type'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'client_name' => $request->input('client_name'),
            'worker' => $request->input('worker'),
            'subject' => $request->input('subject'),
            'step_name' => $request->input('step_name'),
            'status' => $request->get('status'),
            'approval_status' => $request->get('approval_status'),
            'task_contact' => $request->get('task_contact'),

            'page' => $request->input('page'),
            'sort_by' => $request->get('sort_by'),
            'descending' => $request->get('descending') ? 'desc' : 'asc',
            'rows_per_page' => $request->get('rows_per_page'),
        ];

        list($list, $all_request_work_ids) = Task::getWorkSearchList($user->id, $form);

        return response()->json([
            'list' => $list,
            'all_request_work_ids' => $all_request_work_ids //全件作業ID
        ]);
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

    /**
     * [POST]作業リストを取得する
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getWorkList(Request $request): JsonResponse
    {
        $user = \Auth::user();
        // 抽出条件の取得
        $search = [
            'request_work_ids' => $request->get('request_work_ids', []),
        ];
        // 教育フラグ
        $is_education = $request->get('is_education', false);
        // タスク一覧を取得する
        $tasks = Task::getList($user->id, $search, $is_education);
        return response()->json([
            'list' => $tasks,
        ]);
    }
}

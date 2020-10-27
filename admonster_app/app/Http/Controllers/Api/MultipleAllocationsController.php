<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Request as RequestModel;
use App\Models\Task;
use App\Models\User;
use App\Models\RequestWork;

class MultipleAllocationsController extends Controller
{
    public function getInitData(Request $req)
    {
        $user = \Auth::user();

        $request_work_ids = $req->input('request_work_ids');
        $requests = RequestModel::getRelatedDataByIds($request_work_ids, $user->id);

        // 全ユーザ情報を保持（途中で担当者から外れたケースにも対応するため）
        $candidates = User::select(
            'users.id',
            'users.name',
            'users.user_image_path'
        )->get();

        // 作業担当候補者一覧を取得
        $operators = User::getOperatorsByRequestWorkIds(
            $request_work_ids,
            $requests[0]->business_id,
            $requests[0]->step_id
        );

        // 対象案件すべてに関して進行中以降のステータスかどうかをチェック
        foreach ($operators as $key => $value) {
            $operators[$key]->completed_request_works = [];
            $operators[$key]->on_tasks_request_works = [];
            foreach ($requests as $request) {
                if (in_array($value->user_id, explode(',', $request->completed_user_ids))) {
                    $operators[$key]->completed_request_works[] = $request->request_work_id;
                } elseif (in_array($value->user_id, explode(',', $request->on_task_user_ids))) {
                    $operators[$key]->on_tasks_request_works[] = $request->request_work_id;
                }
            }
        }

        // デフォルトの並列度
        $parallel = array_column($requests->toArray(), 'parallel')[0];

        return response()->json([
            'requests' => $requests,
            'candidates' => $candidates,
            'operators' => $operators,
            'parallel' => $parallel,
        ]);
    }

    public function confirm(Request $req)
    {
        $user = \Auth::user();
        $request_work_ids = $req->input('request_work_ids');
        $user_ids = $req->input('user_ids');
        $requests = RequestModel::getRelatedDataByIds($request_work_ids, $user->id);

        // 全ユーザ情報を保持（途中で担当者から外れたケースにも対応するため）
        $candidates = User::select(
            'users.id',
            'users.name',
            'users.user_image_path'
        )->get();

        // 作業担当候補者一覧を取得
        $operators = User::getOperatorsByRequestWorkIds(
            $request_work_ids,
            $requests[0]->business_id,
            $requests[0]->step_id
        );

        /*
        * シミュレーション用にユーザーを作成
        */
        $selected_users = [];
        $loop_num = 0;

        // 均等度をセット
        if ($req->input('evenness') == \Config::get('const.ALLOCATION_EVENNESS.EVEN')) {
            $ratios = [];
            $rem = 100 % count($user_ids);
            $each_divide = (int) floor(100 / count($user_ids));
            foreach ($user_ids as $user_id) {
                if ($rem <= 0) {
                    $rem = 0;
                }
                $ratios[] = $each_divide + $rem;
                $rem -= 1;
            }
            foreach ($user_ids as $user_id) {
                $selected_users[$loop_num]['id'] = $user_id;
                $selected_users[$loop_num]['ratio'] = $ratios[$loop_num];
                $loop_num ++;
            }
        } else {
            $ratios = $req->input('ratios');
            foreach ($user_ids as $user_id) {
                $selected_users[$loop_num]['id'] = $user_id;
                $selected_users[$loop_num]['ratio'] = $ratios[$user_id];
                $loop_num ++;
            }
        }

        // 担当するタスク件数を算出
        $total_task_cnt = count($request_work_ids) * $req->parallel;
        $rollover = 0;
        $unallocated = [];
        foreach ($selected_users as $key => $value) {
            $num = $total_task_cnt * (int) $value['ratio'] / 100;

            if (is_float($num)) {
                // 小数点以下は全て加算後整数になるので後で再度振り分ける
                $seperated = explode('.', strval($num));
                $selected_users[$key]['task_cnt'] = (int) $seperated[0];
                $rollover = $rollover + $num - (int) $seperated[0];

                // 算出した担当件数の整数部分が0になる担当者にはここで振り分けず、未振り分けユーザーの配列に追加
                if ($seperated[0] == 0) {
                    $unallocated[] = $selected_users[$key];
                }
            } else {
                $selected_users[$key]['task_cnt'] = $num;
            }

            foreach ($operators as $operator) {
                if ($selected_users[$key]['id'] == $operator->user_id) {
                    $selected_users[$key]['work_in_process_count'] = $operator->work_in_process_count;
                }
            }
        }

        // 余りの件数があればまず最初に振り分けられなかったユーザーに振り分け
        if ($rollover > 0 && count($unallocated) > 0) {
            if ($req->input('evenness') == \Config::get('const.ALLOCATION_EVENNESS.EVEN')) {
                // 残作業件数の多い順にソート
                // $unallocated = array_values(array_sort($unallocated, function ($value) {
                //     return $value['work_in_process_count'];
                // }));
            } else {
                // 均等度の大きい順にソート
                $unallocated = array_values(array_sort($unallocated, function ($value) {
                    return $value['ratio'];
                }));
            }

            $unallocated = array_reverse($unallocated);

            foreach ($unallocated as $key => $value) {
                if ($rollover > 0) {
                    foreach ($selected_users as $key2 => $value2) {
                        if ($selected_users[$key2]['id'] == $unallocated[$key]['id']) {
                            $selected_users[$key2]['task_cnt'] += 1;
                            $rollover -= 1;
                        }
                    }
                }
            }
        }

        // まだ余りがあれば候補者全員を対象に追加で振り分け
        if ($rollover > 0) {
            $users = unserialize(serialize($selected_users));

            if ($req->input('evenness') == \Config::get('const.ALLOCATION_EVENNESS.EVEN')) {
                // 残作業件数の多い順にソート
                $users = array_values(array_sort($users, function ($value) {
                    return $value['work_in_process_count'];
                }));
                $users = array_reverse($users);
            } else {
                // 均等度の大きい順にソート
                $users = array_values(array_sort($users, function ($value) {
                    return $value['ratio'];
                }));
                $users = array_reverse($users);
            }

            foreach ($users as $key => $value) {
                if ($rollover < 1) {
                    break;
                } else {
                    foreach ($selected_users as $key2 => $value2) {
                        if ($selected_users[$key2]['id'] == $users[$key]['id']) {
                            $selected_users[$key2]['task_cnt'] += 1;
                            $rollover -= 1;
                        }
                    }
                }
            }
        }

        /*
        * シミュレーション
        */
        // シミュレーション用に案件情報のコピーを作成
        $provisinal_requests = unserialize(serialize($requests));
        $new_tasks_cnt = 0;  // 新規発生作業数
        foreach ($provisinal_requests as $key => $value) {
            // 案件の担当者をリセット
            $provisinal_requests[$key]->user_ids = '';

            // 作業中、完了済み担当者はそのまま
            $allocated_user_ids = ($value->completed_user_ids == '') ? [] : explode(',', $value->completed_user_ids);
            $on_task_user_ids = ($value->on_task_user_ids == '') ? [] : explode(',', $value->on_task_user_ids);
            $allocated_user_ids = array_merge($allocated_user_ids, $on_task_user_ids);

            $new_tasks_cnt =  $new_tasks_cnt + ($req->parallel - count($allocated_user_ids));

            // $selected_users = array_reverse($selected_users);
            foreach ($selected_users as $key2 => $selected_user) {
                if (count($allocated_user_ids) >= $req->parallel) {
                    break;
                } else {
                    if (!in_array($selected_user['id'], $allocated_user_ids) && $selected_user['task_cnt'] > 0) {
                        // 担当者を追加
                        $allocated_user_ids[] = $selected_user['id'];
                        $selected_users[$key2]['expecting_allocation_requests'][] = $value;
                        $selected_users[$key2]['task_cnt'] -= 1;
                    }
                }
            }
            $provisinal_requests[$key]->user_ids = implode(',', $allocated_user_ids);
            $provisinal_requests[$key]->parallel = (int) $req->parallel;
        }

        // 担当者単位で表示する担当予定案件情報をセット
        $selected_users_infos = [];
        foreach ($operators as $key => $value) {
            foreach ($selected_users as $selected_user) {
                if ($value->user_id == $selected_user['id']) {
                    $value->expecting_allocation_requests = isset($selected_user['expecting_allocation_requests']) && count($selected_user['expecting_allocation_requests']) > 0 ? $selected_user['expecting_allocation_requests'] : [];
                    $value->ratio = $selected_user['ratio'];
                    $selected_users_infos[] = $value;
                }
            }
        }

        $ratios = $req->input('ratios');

        return response()->json([
            'requests' => $requests,
            'provisinal_requests' => $provisinal_requests,
            'candidates' => $candidates,
            'operators' => $operators,
            'parallel' => $req->parallel,
            'evenness' => $req->evenness,
            'ratios' => $ratios,
            'selected_users_infos' => $selected_users_infos,
            'new_tasks_cnt' => $new_tasks_cnt,
        ]);
    }

    public function store(Request $req)
    {
        $user = \Auth::user();

        // 現時点の案件データ
        $requests = json_decode($req->input('requests'));
        $current_requests = [];
        foreach ($requests as $request) {
            $current_requests[] = json_decode(json_encode($request), true);
        }

        // 登録用データ
        $provisional_requests = json_decode($req->input('provisional_requests'));
        $fixed_requests = [];
        foreach ($provisional_requests as $provisional_request) {
            $fixed_requests[] = json_decode(json_encode($provisional_request), true);
        }

        $started_at = $req->input('started_at');

        // DB登録
        \DB::beginTransaction();
        try {
            foreach ($fixed_requests as $key => $fixed_request) {
                $request_work_id = $fixed_request['request_work_id'];
                $user_ids = explode(',', $fixed_request['user_ids']);

//                $completed_user_ids = [];
//                if (is_array(explode(',', $fixed_request['completed_user_ids']))) {
//                    $completed_user_ids = explode(',', $fixed_request['completed_user_ids']);
//                } else {
//                    $completed_user_ids[] = $fixed_request['completed_user_ids'];
//                }
//
//                $on_task_user_ids = [];
//                if (is_array(explode(',', $fixed_request['on_task_user_ids']))) {
//                    $on_task_user_ids = explode(',', $fixed_request['on_task_user_ids']);
//                } else {
//                    $on_task_user_ids[] = $fixed_request['on_task_user_ids'];
//                }

                $current_user_ids = explode(',', $current_requests[$key]['user_ids']);

                // 排他チェック-------------------------------
                // ほかのユーザにより更新されているデータを取得 => あればエラー
                $tasks = Task::where('request_work_id', $request_work_id)
                    ->whereIn('user_id', $current_user_ids)
                    ->where('is_education', '=', \Config::get('const.FLG.INACTIVE'))
                    ->where('updated_at', '>=', $started_at)
                    ->count();

                if ($tasks) {
                    return response()->json([
                        'result' => 'updated_by_others',
                        'request_work_id' => $request_work_id,
                    ]);
                }

                $request_works = RequestWork::where('id', $request_work_id)
                    ->where('updated_at', '>=', $started_at)
                    ->count();

                if ($request_works) {
                    return response()->json([
                        'result' => 'updated_by_others',
                        'request_work_id' => $request_work_id,
                    ]);
                }
                // 排他チェック-------------------------------

                // 未着手かつ担当から外したユーザがいる場合は物理削除する
                Task::where('request_work_id', $request_work_id)
                    ->whereNotIn('user_id', $user_ids)
                    ->where('status', \Config::get('const.TASK_STATUS.NONE'))
                    ->where('is_education', '=', \Config::get('const.FLG.INACTIVE'))
                    ->where('is_active', '=', \Config::get('const.FLG.ACTIVE'))
                    ->delete();

                // 担当から外したユーザの前回作業がある場合は最新の1件を有効にする
                $latest_inactive_tasks = Task::selectRaw('MAX(tasks.id)')
                    ->where('tasks.request_work_id', $request_work_id)
                    ->whereNotIn('user_id', $user_ids)
                    ->where('is_education', '=', \Config::get('const.FLG.INACTIVE'))
                    ->where('tasks.is_active', \Config::get('const.FLG.INACTIVE'))
                    ->groupBy('tasks.user_id')
                    ->get();

                Task::whereIn('tasks.id', $latest_inactive_tasks)
                    ->where('is_education', '=', \Config::get('const.FLG.INACTIVE'))
                    ->update(['tasks.is_active' => \Config::get('const.FLG.ACTIVE')]);

                $insert_tasks = array();
                $current_time  = Carbon::now();

                // 既に割り振られているユーザIDを除外
                $insert_user_ids = array_values(array_diff($user_ids, $current_user_ids));

                foreach ($insert_user_ids as $user_id) {
                    $request_work_deadline = \DB::table('request_works')
                        ->where('id', $request_work_id)
                        ->value('deadline');

                    $insert_task = [
                        'request_work_id' => $request_work_id,
                        'user_id' => $user_id,
                        'status' => \Config::get('const.TASK_STATUS.NONE'),
                        'is_active' => \Config::get('const.FLG.ACTIVE'),
                        'created_at' => $current_time,
                        'created_user_id' => $user->id,
                        'updated_at' => $current_time,
                        'updated_user_id' => $user->id,
                        'deadline' => $request_work_deadline,
                        'system_deadline' => $request_work_deadline
                    ];
                    array_push($insert_tasks, $insert_task);
                }

                Task::insert($insert_tasks);

                $request_work = RequestWork::find($request_work_id);
                $request_work->touch();
            }

            \DB::commit();

            return response()->json([
                'result' => 'success',
                // 'request_work_id' => $request_work_id,
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
                // 'request_work_id' => $request_work_id,
            ]);
        }
    }
}

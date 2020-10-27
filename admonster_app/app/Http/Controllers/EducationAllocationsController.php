<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as RequestModel;

class EducationAllocationsController extends Controller
{
    public function index(Request $req)
    {
        // GETパラメータで渡された検索条件を設定
        $form = null;
        if ($req->input()) {
            $from = $req->input('from') ? $req->input('from') : '';
            if (is_numeric($from)) {
                $from = (int)$from;
            }

            $to = $req->input('to') ? $req->input('to') : '';
            if (is_numeric($to)) {
                $to = (int)$to;
            }

            $date_type = $req->input('date_type');
            if (is_numeric($date_type)) {
                $date_type = (int)$date_type;
            }

            $form = [
                'request_work_ids' => $req->input('request_work_ids') ? $req->input('request_work_ids') : [],
                'business_name' => $req->input('business_name'),
                'step_name' => $req->input('step_name'),
                'date_type' => $date_type,
                'from' => $from,
                'to' => $to,
                'status' => $req->input('status') ? $req->input('status') : [],
            ];
        }

        return view('education.allocations.index')->with([
            'inputs' => json_encode($form),
        ]);
    }

    public function edit(Request $req, $request_work_id)
    {
        return view('education.allocations.single')->with([
            'inputs' => json_encode([
                'request_work_id' => $request_work_id,
            ]),
        ]);
    }

    public function create(Request $req)
    {
        $user = \Auth::user();

        $request_work_ids = explode(',', $req->request_work_ids);

        return view('education.allocations.multiple')->with([
            'inputs' => json_encode([
                'request_work_ids' => $request_work_ids,
            ]),
        ]);
    }

    public function confirm(Request $req)
    {
        $user = \Auth::user();

        // 配列に変換
        $request_work_ids = explode(',', $req->request_work_ids);
        $user_ids = explode(',', $req->user_ids);
        $is_display_educationals = explode(',', $req->is_display_educationals);

        // 選択されたユーザー情報を作成
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
            $ratios = json_decode($req->ratios, true);
            foreach ($user_ids as $user_id) {
                $selected_users[$loop_num]['id'] = $user_id;
                $selected_users[$loop_num]['ratio'] = $ratios[$user_id];
                $loop_num ++;
            }
        }

        $total_task_cnt = count($request_work_ids) * $req->parallel;
        $rollover = 0;
        $unallocated = [];

        foreach ($selected_users as $key => $value) {
            $num = $total_task_cnt * (int) $value['ratio'] / 100;
            if (is_float($num)) {
                // 小数点以下は次に引き継ぎ
                $seperated = array_map('intval', explode('.', strval($num)));
                $selected_users[$key]['task_cnt'] = $seperated[0];
                $rollover = $rollover + $num - $seperated[0];

                // 割り振られなかった人
                if ($seperated[0] == 0) {
                    $unallocated[] = $selected_users[$key];
                }
            } else {
                $selected_users[$key]['task_cnt'] = $num;
            }
        }

        // 余りの件数があれば振り分け
        if ($rollover > 0) {
            // 均等度の大きい順にソート
            $unallocated = array_values(array_sort($unallocated, function ($value) {
                return $value['ratio'];
            }));
            $unallocated = array_reverse($unallocated);

            foreach ($unallocated as $key => $value) {
                if ($rollover < 1) {
                    break;
                } else {
                    foreach ($selected_users as $key2 => $value2) {
                        if ($selected_users[$key2]['id'] == $unallocated[$key]['id']) {
                            $selected_users[$key2]['task_cnt'] += 1;
                            $rollover -= 1;
                        }
                    }
                }
            }
        }

        $is_education = true;
        $requests = RequestModel::getRelatedDataByIds($request_work_ids, $user->id, $is_education);

        foreach ($requests as $key => $value) {
            $allocated_user_ids = explode(',', $value->user_ids);
            // $completed_user_ids = explode(',', $value->completed_user_ids);
            foreach ($selected_users as $selected_user) {
                if (count($allocated_user_ids) >= $req->pararell) {
                    break;
                } else {
                    if (!in_array($selected_user['id'], $allocated_user_ids)) {
                        $allocated_user_ids[] = $selected_user['id'];
                    }
                }
            }
            $requests[$key]->user_ids = $allocated_user_ids;
        }

        return view('education.allocations.multiple_confirm')->with([
            'inputs' => json_encode([
                'is_display_educationals' => $is_display_educationals,
                'request_work_ids' => $request_work_ids,
                'user_ids' => $user_ids,
                'parallel' => $req->parallel,
                'evenness' => $req->evenness,
                'ratios' => $ratios,
            ]),
        ]);
    }
}

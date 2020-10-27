<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Business;
use App\Models\Step;
use App\Models\User;
use App\Models\WorksUser;
use DB;

class BusinessesController extends Controller
{
    public function index(Request $req)
    {
        $user = \Auth::user();

        $form = [
            'company_name' => $req->input('company_name'),
            'date_type' => $req->input('date_type'),
            'search_from' => $req->input('from'),
            'search_to' => $req->input('to'),
        ];

        $businesses = Business::getList($user->id, $form);

        return response()->json([
            'businesses' => $businesses,
        ]);
    }

    public function show(Request $req)
    {
        $user = \Auth::user();

        $business_id = $req->business_id;

        $business_detail = Business::getDetailDataSetList($user->id, $business_id);

        $steps = Step::getListByBusinessId($business_id);

        // 業務の担当者を取得する
        $candidates = Business::select(
            'users.id',
            'users.name',
            'users.user_image_path'
        )
        ->join('businesses_candidates', 'businesses.id', '=', 'businesses_candidates.business_id')
        ->join('users', 'businesses_candidates.user_id', '=', 'users.id')
        ->where('businesses.id', $business_id)
        ->get();

        // 業務の管理者を取得する
        $admins = Business::select(
            'users.id',
            'users.name',
            'users.user_image_path'
        )
        ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
        ->join('users', 'businesses_admin.user_id', '=', 'users.id')
        ->where('businesses.id', $business_id)
        ->get();

        return response()->json([
            'business_detail' => $business_detail,
            'steps' => $steps,
            'candidates' => $candidates,
            'admins' => $admins
        ]);
    }

    public function store(Request $req)
    {

        $user = \Auth::user();
        $step_id = $req['step_id'];
        $register_user_ids = $req['register_user_ids'];
        $work_user_ids = $req['work_user_ids'];

        // DB登録
        \DB::beginTransaction();
        try {
            // 排他チェック
            // ほかのユーザにより更新されているデータを取得 => あればエラー
            $steps = Step::where('id', $step_id)
                ->where('updated_at', '>=', $req['started_at'])
                ->count();

            $request_work_id = '';
            if ($steps) {
                return response()->json([
                    'result' => 'warning',
                    'request_work_id' => $request_work_id,
                ]);
            }

            //　作業の更新
            $step = Step::find($step_id);
            $step->description = $req['description'];
            $step->time_unit = $req['time_unit'];
            $step->deadline_limit = $req['deadline_limit'];
            $step->updated_user_id = $user->id;
            $step->save();

            // 担当者候補の更新
            WorksUser::where('step_id', $step_id)
                ->whereNotIn('user_id', $register_user_ids)
                ->delete();

            $insert_works_users = array();
            $current_time  = Carbon::now();

            // 既に割り振られているユーザIDを除外
            $result = array_diff($register_user_ids, $work_user_ids);
            $insert_user_ids = array_values($result);

            foreach ($insert_user_ids as $user_id) {
                $insert_works_user = [
                    'step_id' => $step_id,
                    'user_id' => $user_id,
                    'created_at' => $current_time,
                    'created_user_id' => $user->id,
                    'updated_at' => $current_time,
                    'updated_user_id' => $user->id
                ];
                array_push($insert_works_users, $insert_works_user);
            }

            WorksUser::insert($insert_works_users);

            \DB::commit();

            return response()->json([
                'result' => 'success',
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
            ]);
        }
    }

    // 自分が管理者の業務一覧
    public function managedList(Request $request)
    {
        $user = \Auth::user();

        $list = Business::whereHas('users', function ($query) use ($user) {
            $query->where('id', $user->id);
        })->get();

        return response()->json([
            'list' => $list
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Step;
use App\Models\Label;
use App\Models\ItemConfig;

class StepsController extends Controller
{
    public function index(Request $req)
    {
        $user = \Auth::user();
        // 作業マスタ一覧情報を取得
        $steps = Step::select()
            ->where('is_deleted', config('const.FLG.INACTIVE'))
            ->orderBy('id')
            ->get();
        // 全ユーザ情報を取得
        $candidates = User::select(
            'id',
            'name',
            'user_image_path'
        )->get();
        // ラベルデータリストを取得
        $labels = Label::getLangKeySetAll();
        return response()->json([
            'list' => $steps,
            'candidates' => $candidates,
            'labels'=> $labels,
        ]);
    }

    public function getItemConfigs(Request $req)
    {
        $user = \Auth::user();
        // 作業ID
        $step_id = $req->step_id;
        // 作業項目設定リストを取得
        $item_configs = ItemConfig::getRequestContentsItemList($step_id);

        return response()->json([
            'list'=> $item_configs,
        ]);
    }

    public function updateRequestTemplate(Request $req)
    {
        $user = \Auth::user();
        $step_id = $req->step_id;
        $before_work_template = $req->template;

        // DB登録
        \DB::beginTransaction();
        try {
            //　作業の更新
            $step = Step::find($step_id);
            $step->before_work_template = json_encode($before_work_template, JSON_UNESCAPED_UNICODE);
            $step->updated_user_id = $user->id;
            $step->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'result' => 'error',
            ]);
        }
        return response()->json([
            'result' => 'success',
            'step' => $step
        ]);
    }
}

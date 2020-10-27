<?php

namespace App\Http\Viewcomposers;

use Illuminate\View\View;
use Auth;

class UserChargeComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $user = Auth::user();
        if (empty($user)) {
            return;
        }
        // 作業の各担当になっているかを取得、これを使ってhtmlの出し分けを行う。
        // 下記は例。実際のテーブル名が決定してモデルが作成できるようになり次第実装。
        //
        // $isAssignor = $user->isAssignor();
        // $isWorker = $user->isWorker();
        // $isApprover = $user->isApprover();

        // $view->with([
        //     'isAssignor' => $isAssignor,
        //     'isWorker' => $isWorker,
        //     'isApprover' => $isApprover,
        // ]);
        //
    }
}

<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Business;

class WorkersController extends Controller
{
    public function detail(Request $req)
    {
        $user = \Auth::user();
        $worker_id = $req->route('user_id');

        if (!User::find($worker_id)) {
            abort(404);
        }
        // 自分の担当業務の担当者じゃない場合は403を返す
        $list = Business::select()
            ->join('businesses_candidates', 'businesses.id', '=', 'businesses_candidates.business_id')
            ->where('businesses_candidates.user_id', $worker_id)
            ->whereHas('users', function ($query) use ($user) {
                $query->where('id', $user->id);
            })
            ->count();

        if ($list < 1) {
            abort(403, 'Unauthorized action.');
        }

        return view('workers.show')->with([
            'user_id' => $req->user_id,
        ]);
    }
}

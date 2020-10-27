<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;

class TasksController extends Controller
{
    // 一覧
    public function index(Request $req)
    {
        $user = \Auth::user();

        // 検索条件の取得
        $form = [
            'business_name' => $req->get('business_name'),
            'step_name' => $req->get('step_name'),
            'date_type' => $req->get('date_type'),
            'from' => $req->get('from'),
            'to' => $req->get('to'),
            'status' => $req->get('status'),
            'unverified' => $req->get('unverified'),
            'page' => $req->get('page'),
            'sort_by' => $req->get('sort_by'),
            'descending' => $req->get('descending') ? 'desc' : 'asc',
            'rows_per_page' => $req->get('rows_per_page'),
        ];

        // 検索の有無
        $search_flg = true;

        $tasks = Task::getRelatedDataSetList($user->id, $form, $search_flg);
        return response()->json([
            'tasks' => $tasks,
        ]);
    }
}

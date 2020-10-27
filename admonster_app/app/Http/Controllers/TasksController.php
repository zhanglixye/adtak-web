<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TasksController extends Controller
{
    // 一覧
    public function index(Request $req)
    {
        // GETパラメータで渡された検索条件を設定
        $form = null;
        if ($req->input()) {
            $form = [
                'status' => $req->input('status') ? $req->input('status') : [],
            ];
        }

        return view('tasks.index')->with([
            'inputs' => json_encode($form),
        ]);
    }
}

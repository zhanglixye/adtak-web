<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class BusinessStatesController extends Controller
{
    public function index(Request $req)
    {
        // GETパラメータで渡された検索条件を設定
        $form = null;
        if ($req->input()) {
            $form = [
                'business_name' => $req->input('business_name'),
                'date_type' => $req->input('date_type'),
                'from' => $req->input('from') ? $req->input('from') : '',
                'to' => $req->input('to') ? $req->input('to') : '',
            ];
        }

        return view('business_states.index')->with([
            'inputs' => json_encode($form),
        ]);
    }
}

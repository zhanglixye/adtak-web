<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    // 一覧
    public function index()
    {
        return view('order.orders.index');
    }

    // 案件設定
    public function edit(Request $req)
    {
        // GETパラメータで渡された検索条件を設定
        $form = [
            'order_id' => $req->order_id,
        ];

        return view('order.orders.edit')->with([
            'inputs' => json_encode($form),
        ]);
    }

    // 案件の項目設定
    public function itemEdit(Request $req)
    {
        $form = [
            'order_id' => $req->order_id,
        ];

        return view('order.orders.item_edit')->with([
            'inputs' => json_encode($form),
        ]);
    }
}

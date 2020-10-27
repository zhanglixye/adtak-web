<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderDetailsController extends Controller
{
    // 一覧画面
    public function index(Request $req)
    {
        $order_id = (int)$req->order_id;

        return view('order.order_details.index')->with([
            'order_id' => $order_id,
        ]);
    }

    // 詳細画面
    public function show(Request $req)
    {
        $order_id = (int)$req->order_id;
        $order_detail_id = (int)$req->order_detail_id;

        return view('order.order_details.show')->with([
            'order_id' => $order_id,
            'order_detail_id' => $order_detail_id,
        ]);
    }
}

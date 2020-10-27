<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Delivery;

class DeliveriesController extends Controller
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

        return view('deliveries.index')->with([
            'inputs' => json_encode($form),
        ]);
    }

    public function detail(Request $req)
    {
        $request_work_id = $req->request_work_id;
        // 納品データ取得
        $delivery = Delivery::getByRequestWorkId($request_work_id);

        return view('deliveries.detail')->with([
            'request_work_id' => $request_work_id,
            'delivery' => $delivery ? $delivery : new Delivery(),
            'reference_mode' => $req->input('reference_mode', 'false'),
        ]);
    }
}

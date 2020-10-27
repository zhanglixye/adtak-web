<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImportedFilesController extends Controller
{
    public function index(Request $req)
    {
        // GETパラメータで渡された検索条件を設定
        $form = null;
        if ($req->input()) {
            $form = [
                'imported_file_id' => $req->input('imported_file_id'),
            ];
        }

        return view('imported_files.index')->with([
            'inputs' => json_encode($form),
        ]);
    }

    public function confirm(Request $req)
    {
        $business_id = $req->business_id;
        $tmp_file_info = [
            'file_name' => $req->file_name,
            'tmp_file_dir' => $req->tmp_file_dir,
            'tmp_file_path' => $req->tmp_file_path,
        ];

        return view('imported_files.confirm')->with([
            'inputs' => json_encode([
                'business_id' => $business_id,
                'tmp_file_info' => $tmp_file_info,
            ]),
        ]);
    }

    public function orderConfirm(Request $req)
    {
        /** @var int|null */
        $order_id = is_numeric($req->input('order_id')) ? intval($req->input('order_id')) : null;
        $order_name = $req->input('order_name');
        $tmp_file_info = [
            'file_name' => $req->input('file_name'),
            'tmp_file_dir' => $req->input('tmp_file_dir'),
            'tmp_file_path' => $req->input('tmp_file_path'),
        ];

        return view('imported_files.order_confirm')->with([
            'inputs' => json_encode([
                'tmp_file_info' => $tmp_file_info,
                'order_name' => $order_name,
                'order_id' => $order_id,
            ]),
        ]);
    }

    public function orderSetting(Request $req)
    {
        $order_name = $req->input('order_name');
        $tmp_file_info = [
            'file_name' => $req->input('file_name'),
            'tmp_file_dir' => $req->input('tmp_file_dir'),
            'tmp_file_path' => $req->input('tmp_file_path'),
        ];
        $readonly =  filter_var($req->input('readonly'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $order_id = is_numeric($req->input('order_id')) ? intval($req->input('order_id')) : null;

        return view('imported_files.order_setting')->with([
            'inputs' => json_encode([
                'tmp_file_info' => $tmp_file_info,
                'order_name' => $order_name,
                'readonly' => $readonly,
                'order_id' => $order_id,
            ]),
        ]);
    }
}

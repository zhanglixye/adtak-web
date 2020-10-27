<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\RequestWork;
use App\Models\RequestMail;
use App\Models\RequestFile;
use App\Models\Task;
use App\Models\Approval;
use App\Models\Delivery;
use App\Models\User;
use App\Models\Step;
use Carbon\Carbon;

class RequestWorksController extends Controller
{
    public function index(Request $req)
    {
        // GETパラメータで渡された検索条件を設定
        $form = null;
        if ($req->input()) {
            $form = [
                'title' => $req->input('title'),
                'request_work_ids' => $req->input('request_work_ids'),
                'request_work_name' => $req->input('request_work_name'),
                'business_name' => $req->input('business_name'),
                'step_name' => $req->input('step_name'),
                // 'client_name' => $req->input('client_name'),
                'user_name' => $req->input('user_name'),
                'date_type' => $req->input('date_type'),
                'from' => $req->input('from'),
                'to' => $req->input('to'),
                'request_file_name' => $req->input('request_file_name'),
                'request_mail_subject' => $req->input('request_mail_subject'),
                'request_mail_to' => $req->input('request_mail_to'),
                'status' => $req->input('status'),
                'self' => $req->input('self'),
                'completed' => $req->input('completed'),
                'inactive' => $req->input('inactive'),
                'excluded' => $req->input('excluded'),
                // 'page' => $request->get('page'),
                // 'sort_by' => $request->get('sort_by'),
                // 'descending' => $request->get('descending') ? 'desc' : 'asc',
                // 'rows_per_page' => $request->get('rows_per_page'),
            ];
        }

        return view('request_works.index')->with([
            'inputs' => json_encode($form),
        ]);
    }

    public function show(Request $req)
    {
        return view('request_works.show')->with([
            'request_work_id' => $req->request_work_id,
        ]);
    }
}

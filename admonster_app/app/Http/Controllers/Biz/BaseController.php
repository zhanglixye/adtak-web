<?php

namespace App\Http\Controllers\Biz;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Step;

class BaseController extends Controller
{
    public function create(Request $req)
    {
        $business_id = (int)$req->business_id;
        $step_id = (int)$req->step_id;
        $step_name = Step::where('id', $step_id)->value('name');

        return view('biz.base')->with([
            'step_name' => $step_name,
            'business_id' => $business_id,
            'step_id' => $step_id,
            'request_work_id' => $req->request_work_id,
            'task_id' => $req->task_id,
            'reference_mode' => $req->input('reference_mode', 'false'),
        ]);
    }
}

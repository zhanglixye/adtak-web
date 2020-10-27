<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function edit(Request $req, $request_work_id, $task_id)
    {
        return view('verification.edit')->with([
            'inputs' => json_encode([
                'request_work_id' => $request_work_id,
                'task_id' => $task_id,
            ]),
        ]);
    }
}

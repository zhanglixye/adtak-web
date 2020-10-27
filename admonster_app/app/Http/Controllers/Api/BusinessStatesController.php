<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;

class BusinessStatesController extends Controller
{
    public function index(Request $req)
    {
        $user = \Auth::user();

        $form = [
            'business_name' => $req->input('business_name'),
            'date_type' => $req->input('date_type'),
            'search_from' => $req->input('from'),
            'search_to' => $req->input('to'),
            'completed' => $req->input('completed'),
            'inactive' => $req->input('inactive'),
        ];

        $business_states = Business::searchBusinessStateList($user->id, $form);
        $business_states = $business_states->toArray();
        $base = $business_states[0];
        // 【依頼】を先頭に追加
        array_unshift($business_states, self::requestAll($base));
        // 【完了】を末尾に追加
        array_push($business_states, self::requestCompleted($base));

        $business_state_conditions = Business::searchBusinessStateConditions($user->id);
        $search_conditions = [
            'businesses' => $business_state_conditions->pluck('business_id', 'business_name'),
        ];

        return response()->json([
            'business_states' => $business_states,
            'search_conditions' => $search_conditions,
        ]);
    }

    private function requestAll($base)
    {
        return array(
            'step_name' => '【依頼】',
            'request_count' => $base->request_all_count,
            'all_excluded_count' => $base->request_excluded_count,
        );
    }

    private function requestCompleted($base)
    {
        return array(
            'step_name' => '【完了】',
            'all_completed_count'  => $base->request_completed_count,
        );
    }
}

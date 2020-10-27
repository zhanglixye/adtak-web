<?php
/**
 * Created by PhpStorm.
 * User: yonghua-you
 * Date: 2020/07/03
 * Time: 14:44
 */

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\EducationalWorksUser;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\BusinessesCandidate;
use App\Models\BusinessesAdmin;
use App\Models\Step;
use App\Models\WorksUser;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BusinessesController extends Controller
{
    public function index(Request $req)
    {
        $user = \Auth::user();
        $Businesses = Business::getBusinessesList();
        return response()->json([
            'status' => 200,
            'businesses' => $Businesses,
        ]);
    }

    public function detail(Request $req)
    {
        $user = \Auth::user();
        $business_id = $req->business_id;
        $business_detail = Business::getBusinessById($business_id);
        $admin_users = User::getAdminUsersByBusinessId($business_id);
        $admin_user_ids = [];
        foreach ($admin_users as $admin_user) {
            array_push($admin_user_ids, $admin_user->id);
        }
        $admin_candidate_users = User::getCandidateUsersByuserIds($admin_user_ids);
        $operator_users = User::getOperatorsByBusinessId($business_id);
        $operator_user_ids = [];
        foreach ($operator_users as $operator_user) {
            array_push($operator_user_ids, $operator_user->id);
        }
        $operator_candidate_users = User::getCandidateUsersByuserIds($operator_user_ids);
        $steps = Step::getListByBusinessId($business_id);
        return response()->json([
            'status' => 200,
            'business_detail' => $business_detail,
            'admin_users' => $admin_users,
            'admin_candidate_users' => $admin_candidate_users,
            'operator_users' => $operator_users,
            'operator_candidate_users' => $operator_candidate_users,
            'steps' => $steps
        ]);
    }

    public function deleteCandidates(Request $req)
    {
        $business_id = $req->businessId;
        $user_ids = $req->deleteCandidateIds;
        \DB::beginTransaction();
        try {
            $delete_sum = BusinessesCandidate::where('business_id', $business_id)
                ->whereIn('user_id', $user_ids)->delete();
            \DB::commit();
            return response()->json([
                'status' => 200,
                'sum' => $delete_sum
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function addCandidates(Request $req)
    {

        $business_id = $req->businessId;
        $operator_candidate_users = $req->operatorCandidateUsers;
        \DB::beginTransaction();
        try {
            $user = \Auth::user();
            foreach ($operator_candidate_users as $operator_candidate_user) {
                $businesses_Candidate = new BusinessesCandidate;
                $businesses_Candidate->business_id = $business_id;
                $businesses_Candidate->user_id = $operator_candidate_user['id'];
                $businesses_Candidate->mail_address = $operator_candidate_user['email'];
                $businesses_Candidate->updated_user_id = $user->id;
                $businesses_Candidate->created_user_id = $user->id;
                $businesses_Candidate->save();
            }
            \DB::commit();
            return response()->json([
                'status' => 200,
                'result' => 'success'
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function addAdministrators(Request $req)
    {

        $business_id = $req->businessId;
        $admin_candidate_users = $req->adminCandidateUsers;
        \DB::beginTransaction();
        try {
            $user = \Auth::user();
            foreach ($admin_candidate_users as $admin_candidate_user) {
                $businesses_admin = new BusinessesAdmin;
                $businesses_admin->business_id = $business_id;
                $businesses_admin->user_id = $admin_candidate_user['id'];
                $businesses_admin->updated_user_id = $user->id;
                $businesses_admin->created_user_id = $user->id;
                $businesses_admin->save();
            }
            \DB::commit();
            return response()->json([
                'status' => 200,
                'result' => 'success'
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function deleteAdministrators(Request $req)
    {
        $business_id = $req->businessId;
        $user_ids = $req->deleteCandidateIds;
        \DB::beginTransaction();
        try {
            $delete_sum = BusinessesAdmin::where('business_id', $business_id)
                ->whereIn('user_id', $user_ids)->delete();
            \DB::commit();
            return response()->json([
                'status' => 200,
                'sum' => $delete_sum
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function copy(Request $req)
    {
        $business_id = $req->business_id;
        $operator_users = $req->operators;
        $step_ids = Step::getStepsByBusinessId($business_id);

        \DB::beginTransaction();
        try {
            $user = \Auth::user();
            $works_user_params = [];
            $educational_works_user_params = [];
            $time_now = Carbon::now();
            foreach ($step_ids as $step_id) {
                foreach ($operator_users as $operator_user) {
                    $work_user = WorksUser::getWorksUserByUserIdAndStepId($operator_user['id'], $step_id->id);

                    if (!count($work_user->toArray())) {
                        array_push($works_user_params, [
                            'step_id'=> $step_id->id,
                            'user_id' => $operator_user['id'],
                            'created_at' => $time_now,
                            'created_user_id' => $user->id,
                            'updated_at' => $time_now,
                            'updated_user_id'=>  $user->id]);
                    }
                    $education_work_user = EducationalWorksUser::getEducationWorkUserByUserIdAndStepId(
                        $operator_user['id'],
                        $step_id->id
                    );
                    if (!count($education_work_user->toArray())) {
                        array_push($educational_works_user_params, [
                            'step_id'=> $step_id->id,
                            'user_id' => $operator_user['id'],
                            'created_at' => $time_now,
                            'created_user_id' => $user->id,
                            'updated_at' => $time_now,
                            'updated_user_id'=>  $user->id]);
                    }
                }
            }
            //作業担当者を追加
            if (count($works_user_params)) {
                \DB::table('works_users')->insert($works_user_params);
            }
            // 教育担当者を追加
            if (count($educational_works_user_params)) {
                \DB::table('educational_works_users')->insert($educational_works_user_params);
            }
            \DB::commit();
            return response()->json([
                'status' => 200,
                'result' => 'success'
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     *
     *担当者を追加
     * @param Request $req
     * @return JsonResponse
     */
    public function addWorksUsers(Request $req): JsonResponse
    {

        $work_candidate_users = $req->get('work_candidate_users');
        $step_id = $req->get('step_id');
        $work_candidate_users_params = [];
        $time_now = Carbon::now();
        \DB::beginTransaction();
        try {
            $user = \Auth::user();
            foreach ($work_candidate_users as $order_admin_candidate_user) {
                array_push($work_candidate_users_params, [
                    'step_id' => $step_id,
                    'user_id' => $order_admin_candidate_user['id'],
                    'created_at' => $time_now,
                    'created_user_id' => $user->id,
                    'updated_at' => $time_now,
                    'updated_user_id'=>  $user->id
                ]);
            }
            if (count($work_candidate_users_params)) {
                \DB::table('works_users')->insert($work_candidate_users_params);
            }
            \DB::commit();
            return response()->json([
                'status' => 200,
                'result' => 'success'
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     *
     *担当者を削除
     * @param Request $req
     * @return JsonResponse
     */
    public function deleteWorksUser(Request $req): JsonResponse
    {
        $delete_works_user_ids = $req->get('delete_works_user_ids');
        $step_id = $req->get('step_id');
        \DB::beginTransaction();
        try {
            $delete_sum = WorksUser::where('step_id', $step_id)
                ->whereIn('user_id', $delete_works_user_ids)->delete();
            \DB::commit();
            return response()->json([
                'status' => 200,
                'sum' => $delete_sum
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }


    /**
     *
     *演習担当者を追加
     * @param Request $req
     * @return JsonResponse
     */
    public function addEducationalWorksUsers(Request $req): JsonResponse
    {

        $educational_work_candidate_users = $req->get('educational_work_candidate_users');
        $step_id = $req->get('step_id');
        $educational_work_candidate_params = [];
        $time_now = Carbon::now();
        \DB::beginTransaction();
        try {
            $user = \Auth::user();
            foreach ($educational_work_candidate_users as $educational_work_candidate_user) {
                array_push($educational_work_candidate_params, [
                    'step_id' => $step_id,
                    'user_id' => $educational_work_candidate_user['id'],
                    'created_at' => $time_now,
                    'created_user_id' => $user->id,
                    'updated_at' => $time_now,
                    'updated_user_id'=>  $user->id
                ]);
            }
            if (count($educational_work_candidate_params)) {
                \DB::table('educational_works_users')->insert($educational_work_candidate_params);
            }
            \DB::commit();
            return response()->json([
                'status' => 200,
                'result' => 'success'
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     *
     *演習担当者を削除
     * @param Request $req
     * @return JsonResponse
     */
    public function deleteEducationalWorksUser(Request $req): JsonResponse
    {
        $delete_educational_works_user_ids = $req->get('delete_educational_works_user_ids');
        $step_id = $req->get('step_id');
        \DB::beginTransaction();
        try {
            $delete_sum = EducationalWorksUser::where('step_id', $step_id)
                ->whereIn('user_id', $delete_educational_works_user_ids)->delete();
            \DB::commit();
            return response()->json([
                'status' => 200,
                'sum' => $delete_sum
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }
}

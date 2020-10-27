<?php
/**
 * Created by PhpStorm.
 * User: yonghua-you
 * Date: 2020/09/28
 * Time: 14:19
 */

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\UploadFileManager\Uploader;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
    /**
     * 個人設定情報を取得
     * @param Request $req
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(Request $req): JsonResponse
    {
        $user = \Auth::user();
        return response()->json([
            'user' => $user,
            'status' => 200
        ]);
    }

    /**
     * ユーザー画像を更新
     * @param Request $req
     * @return JsonResponse
     * @throws \Exception
     */
    public function updateUserImage(Request $req): JsonResponse
    {
        $image_file = $req->input('image_file');
        $user_id = $req->input('user_id');
        $user = \Auth::user();
        \DB::beginTransaction();
        try {
            if ($user_id != $user->id) {
                throw new \Exception('updated user is not user who is logining');
            }
            if (is_null($image_file)) {
                throw new \Exception('image is null');
            }
            $cloud_front_domain_name = \Config::get('app.cloud_front_url');
            $file_name = $image_file[0]['file_name'];
            $file_path = 'images/users/' . $user_id . '/' . md5(strval(time())) . '/' . $file_name;
            list(, $fileData) = explode(';', $image_file[0]['file_data']);
            list(, $fileData) = explode(',', $fileData);

            $file_contents = base64_decode($fileData);
            $s3_file_path = Uploader::uploadToS3($file_contents, $file_path);
            $user_image_path = $cloud_front_domain_name . '/' . $file_path;
            // 過去の画像がデフォルト画像じゃない時、過去の画像を削除
            //$old_user_image_path = \DB::table('users')
            //    ->select('user_image_path')
            //    ->where('id', $user_id)
            //    ->get()
            //    ->toArray();
            //$old_user_image_path = json_decode(json_encode($old_user_image_path), true);
            //if ($old_user_image_path[0]['user_image_path'] != '/images/dummy_icon.png') {
            //  $old_user_image_path = explode(
            //       $cloud_front_domain_name . "/",
            //        $old_user_image_path[0]['user_image_path']
            //    )[1];
            //    $flg = Uploader::deleteFromS3($old_user_image_path);
            //}
            User::where('id', $user_id)
                ->update(['user_image_path' => $user_image_path]);
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
     * パスワードを更新する
     * @param Request $req
     * @return JsonResponse
     * @throws \Exception
     */
    public function updatePassword(Request $req): JsonResponse
    {
        $user_id = $req->input('user_id');
        $old_password = $req->input('old_password');
        $new_password = $req->input('new_password');
        $user = \Auth::user();
        $time_now = Carbon::now();


        \DB::beginTransaction();
        try {
            if ($user_id != $user->id) {
                throw new \Exception('updated user is not user who is logining');
            }
            if (is_null($new_password)) {
                throw new \Exception('new_password is null');
            }
            if (is_null($old_password)) {
                throw new \Exception('old_password is null');
            }
            // 過去のパスワードを確認
            $res = \DB::table('users')->where('id', $user_id)->select('password')->first();
            if (!Hash::check($old_password, $res->password)) {
                throw new \Exception('old_password is wrong');
            }
            $user = User::find($user_id);
            $user->password = Hash::make($new_password);
            $user->password_changed_date = $time_now;
            $user->updated_user_id = $user->id;
            $user->save();

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
     * プロフィール情報を更新
     * @param Request $req
     * @return JsonResponse
     * @throws \Exception
     */
    public function updateProfile(Request $req): JsonResponse
    {
        $user_id = $req->input('user_id');
        $nickname = $req->input('nickname');
        $birthday = $req->input('birthday');
        $sex = $req->input('sex');
        \DB::beginTransaction();
        try {
            $user = User::find($user_id);
            if ($nickname != $user->nickname) {
                $user->nickname = $nickname;
            }
            if ($birthday != $user->birthday) {
                $user->birthday = $birthday;
            }
            if ($sex != $user->sex) {
                $user->sex = $sex;
            }
            $user->save();

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
}

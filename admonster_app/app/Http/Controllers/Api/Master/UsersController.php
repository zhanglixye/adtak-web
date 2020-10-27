<?php
/**
 * Created by PhpStorm.
 * User: yonghua-you
 * Date: 2020/07/31
 * Time: 19:03
 */

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\SendMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Services\UploadFileManager\Uploader;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
    public function index(Request $req)
    {
        $user = \Auth::user();
        //検索条件の取得
        $form = [
            'id' => $req->input('id'),
            'name' => $req->input('name'),
            'email' => $req->input('email'),
            'page' => $req->input('page'),
            'sort_by' => $req->get('sort_by'),
            'descending' => $req->get('descending') ? 'desc' : 'asc',
            'rows_per_page' => $req->get('rows_per_page'),
        ];
        $users = User::getRelatedUserList($form);
        return response()->json([
            'status' => 200,
            'users' => $users,
        ]);
    }

    public function addUser(Request $req)
    {
        $new_user_array = $req->input('user');
        $user = \Auth::user();
        \DB::beginTransaction();

        try {
            if (is_null($new_user_array['surname'])) {
                throw new \Exception('surname is null');
            }
            if (is_null($new_user_array['name'])) {
                throw new \Exception('name is null');
            }
            if (is_null($new_user_array['email'])) {
                throw new \Exception('email is null');
            }
            if (is_null($new_user_array['password'])) {
                throw new \Exception('password is null');
            }
            // メールアドレスをチェック
            if (User::where('email', $new_user_array['email'])->exists()) {
                throw new \Exception('this email address was also used');
            }
            // ユーザーを追加
            $new_user = new User();
            $new_user->name = trim($new_user_array['surname']).' '.trim($new_user_array['name']);
            $new_user->email = $new_user_array['email'];
            $new_user->password = Hash::make($new_user_array['password']);
            if (!is_null($new_user_array['nickname'])) {
                $new_user->nickname = $new_user_array['nickname'];
            }
            if (!is_null($new_user_array['sex'])) {
                $new_user->sex = $new_user_array['sex'];
            }
            if (!is_null($new_user_array['birthday'])) {
                $new_user->birthday = $new_user_array['birthday'];
            }
            if (!is_null($new_user_array['postal_code'])) {
                $new_user->postal_code = $new_user_array['postal_code'];
            }
            if (!is_null($new_user_array['address'])) {
                $new_user->address = $new_user_array['address'];
            }
            if (!is_null($new_user_array['tel'])) {
                $new_user->tel = $new_user_array['tel'];
            }
            $new_user->timezone = $new_user_array['timezone'];
            $new_user->updated_user_id = $user->id;
            $new_user->created_user_id = $user->id;
            $new_user->save();

            // 画像ファイルを保存する
            $cloud_front_domain_name = \Config::get('app.cloud_front_url');
            if (count($new_user_array['image'])) {
                $file_name = $new_user_array['image'][0]['file_name'];
                $file_path = 'images/users/' . $new_user->id . '/' . md5(strval(time())) . '/' . $file_name;
                list(, $fileData) = explode(';', $new_user_array['image'][0]['file_data']);
                list(, $fileData) = explode(',', $fileData);
                $file_contents = base64_decode($fileData);
                $s3_file_path = Uploader::uploadToS3($file_contents, $file_path);
                $user_image_path = $cloud_front_domain_name . '/' . $file_path;
            } else {
                // デフォルト画像
                $user_image_path = '/images/dummy_icon.png';
            }
            // ユーザー画像カラムを追加
            User::where('id', $new_user->id)
                ->update(['user_image_path' => $user_image_path]);
            // 本文生成
            $mail_address = $new_user->email;
            $user_name = $new_user->name;
            $password = $new_user_array['password'];
            $app_name = \Config::get('app.name');
            $login_url = \Config::get('app.url') .'/login';

            $mail_body = view('emails.user_created')
                ->with('user_name', $user_name)
                ->with('mail_address', $mail_address)
                ->with('password', $password)
                ->with('login_url', $login_url)
                ->with('app_name', $app_name);

            $send_mail = new SendMail;
            $send_mail->cc = null;
            $send_mail->request_work_id = null;
            $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
            $send_mail->subject = '【' . $app_name . '】ユーザー登録完了のお知らせ';
            $send_mail->body = $mail_body;
            $send_mail->created_user_id = \Auth::user()->id;
            $send_mail->updated_user_id = \Auth::user()->id;
            $send_mail->content_type = \Config::get('const.CONTENT_TYPE.HTML');
            $send_mail->to = $mail_address;
            $send_mail->save();

            // 処理キュー登録（send mail）
            $queue = new Queue;
            $queue->process_type = config('const.QUEUE_TYPE.MAIL_SEND');
            $queue->argument = json_encode(['mail_id' => (int)$send_mail->id]);
            $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
            $queue->created_user_id = \Auth::user()->id;
            $queue->updated_user_id = \Auth::user()->id;
            $queue->save();

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

    public function updatePassword(Request $req)
    {
        $user_id = $req->input('userId');
        $new_password = $req->input('newPassword');
        $user = \Auth::user();
        $time_now = Carbon::now();
        if (is_null($user_id)) {
            throw new \Exception('userId is null');
        }
        if (is_null($new_password)) {
            throw new \Exception('password is null');
        }
        \DB::beginTransaction();
        try {
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
}

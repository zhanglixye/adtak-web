<?php

namespace App\Http\Controllers\GuestClient\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\GuestClient;
use App\Models\Queue;
use App\Models\SendMail;
use Auth;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating guest_clients for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
        logout as performLogout;
    }

    protected $guard = 'guest_client';

    public function __construct()
    {
        $this->middleware('guest:guest_client');
    }

    public function username()
    {
        return 'token';
    }

    protected function guard()
    {
        return \Auth::guard('guest_client');
    }

    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    // パスワード発行画面
    public function showIssuePasswordForm(Request $request)
    {
        if (Auth::guard('web')->user()) {
            // ログイン中のusersアカウントでのアクセスの場合
            return redirect('/home');
        }

        $token = $request->token;

        if (!$token) {
            abort(404);
        }

        $client = GuestClient::where('token', $token)->first();
        if (!$client) {
            abort(401, 'No guest_client record.');
        }

        return view('guest_client.auth.password_issue', compact('token'));
    }

    // 認証画面
    public function showLoginForm(Request $request)
    {
        if (Auth::guard('web')->user()) {
            // ログイン中のusersアカウントでのアクセスの場合
            return redirect('/home');
        }

        $token = $request->token;
        $is_reissue = $request->is_reissue;

        if (!$token) {
            abort(404);
        }

        $client = GuestClient::where('token', $token)->first();
        if (!$client) {
            abort(401, 'No guest_client record.');
        }

        return view('guest_client.auth.login', compact('token', 'is_reissue'));
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();

        return $this->loggedOut($request) ? : redirect()->route('guest_client.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('token', 'password');

        if (Auth::guard('guest_client')->validate($credentials)) {
            $client = GuestClient::where('token', $request->token)->first();

            // 有効期限期限切れ
            if ($client->expired_at < Carbon::now()) {
                return redirect('/guest_client/issue_password?token='.$request->token.'&password_error=false')->withErrors([
                    'password_expired' => __('auth.password_expired_and_reissued'),
                ]);
            }

            // 認証
            if (Auth::guard('guest_client')->attempt($credentials)) {
                // 成功
                return redirect('/client/requests/'.$client->request_id);
            } else {
                // 失敗
                return redirect('/guest_client/login');
            }
        }

        if ($request->token && GuestClient::where('token', $request->token)->first()) {
            $token = $request->token;
            return redirect('/guest_client/login?token='.$token.'&password_error=true')->withErrors([
                'password' => __('auth.password_failed'),
                ]);
        } else {
            return $this->sendFailedLoginResponse($request);
        }
    }

    public function issuePassword(Request $request)
    {
        $token = $request->token;
        if (!$token) {
            return redirect('/guest_client/issue_password?token='.$token.'&password_error=true')->withErrors([
                'password' => __('auth.issue_password_failed'),
                ]);
        }

        $client = GuestClient::where('token', $token)->first();
        if (!$client) {
            abort(401, 'No guest_client record.');
        }

        // DB登録
        \DB::beginTransaction();
        try {
            // パスワード生成（数字6桁）
            $password = '';
            for ($i = 0; $i < 6; $i++) {
                $password .= mt_rand(0, 9);
            }
            $hashed = Hash::make($password);
            $client->password = $hashed;
            $client->expired_at = Carbon::now()->addMinutes(10);
            $client->updated_at = Carbon::now();
            $client->updated_user_id = 0;
            $client->save();

            // $client->email にメールを送信
            $mail_body_data = [
                'send_to_email' => $client->email,
                'request_id' => $client->request_id,
                'password' => $password,
                'time_limit' => 10,
            ];

            $send_mail = new SendMail;
            $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
            $send_mail->to = $client->email;
            $send_mail->subject = '【' . \Config::get('app.name') . '】パスワードのご連絡（依頼ID：'.$client->request_id.'）';
            $send_mail->content_type = \Config::get('const.CONTENT_TYPE.HTML');
            $send_mail->body = $this->makeMailBody($mail_body_data);
            $send_mail->created_user_id = 0;
            $send_mail->updated_user_id = 0;
            $send_mail->save();

            // メール送信キューを登録
            $queue = new Queue;
            $queue->process_type = \Config::get('const.QUEUE_TYPE.MAIL_SEND');
            $queue->argument = json_encode(['mail_id' => $send_mail->id]);
            $queue->queue_status = \Config::get('const.QUEUE_STATUS.PREVIOUS');
            $queue->created_user_id = 0;
            $queue->updated_user_id = 0;
            $queue->save();

            \DB::commit();

            return redirect(route('guest_client.login', ['token' => $token, 'is_reissue' => isset($request->is_reissue) ? $request->is_reissue : false]));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return redirect('/guest_client/issue_password?token='.$token.'&password_error=false')->withErrors([
                'issue_password' => __('auth.issue_password'),
                ]);
        }
    }

    public function makeMailBody($mail_body_data)
    {
        return \View::make('guest_client.auth.emails/issue_password')
            ->with($mail_body_data)
            ->render();
    }
}

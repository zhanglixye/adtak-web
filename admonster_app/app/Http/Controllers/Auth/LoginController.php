<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    // use AuthenticatesUsers;
    use AuthenticatesUsers {
        logout as performLogout;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/tasks';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function logout(Request $request)
    {
        $this->performLogout($request);

        return redirect()->route('login');
    }

    // ログイン後の画面をユーザー別で分ける
    // /vendor/laravel/framework/src/Illuminate/Foundation/Auth/AuthenticatesUsers.phpのauthenticatedメソッドをoverride
    protected function authenticated(Request $request, $user)
    {
        $administrating_businnesses = User::find($user->id)->businesses;
        $cnt = $administrating_businnesses->count();

        if ($cnt > 0) {
            // 業務の管理者
            return redirect()->intended('/management/requests');
        } else {
            // どの業務の管理者でもない
            return redirect()->intended('/tasks');
        }

        // if ($cnt > 1) {
        //     // 複数の業務の管理者
        //     return redirect()->intended('/businesses');
        // } elseif ($cnt == 1) {
        //     // 1つだけの業務の管理者 => その業務の詳細ページにリダイレクト
        //     return redirect()->intended('/businesses/'.$administrating_businnesses->first()->id);
        // } elseif ($cnt < 1) {
        //     // どの業務の管理者でもない
        //     return redirect()->intended('/tasks');
        // }
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\GuestClient;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            if ($guard == 'guest_client') {
                // guest_clientsでログイン中の場合
                $logged_in_client_email = Auth::guard($guard)->user()->email;
                if ($request->token) {
                    // トークンマッチチェック
                    $client = GuestClient::where('token', $request->token)->first();
                    if ($client && $client->email == $logged_in_client_email) {
                        // 同じemailに発行されたトークンの場合 => 該当のクライアントページへ遷移
                        return redirect('/client/requests/'.$client->request_id);
                    } else {
                        // 別のemailに発行されたトークンの場合
                        abort(404, 'No guest_client record.');
                    }
                } else {
                    // トークンがない場合
                    abort(404, 'No guest_client record.');
                }
            } else {
                // usersでログイン中の場合
                return redirect('/home');
            }
        }

        return $next($request);
    }
}

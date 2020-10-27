<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckForGuestClient
{

    public function handle($request, Closure $next)
    {
        $request_id = $request->route()->parameter('request_id');
        $token = $request->route()->parameter('token');

        if (Auth::guard('guest_client')->guest()) {
            //
        }

        return $next($request);
    }

    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('client.auth.login');
        }
    }
}

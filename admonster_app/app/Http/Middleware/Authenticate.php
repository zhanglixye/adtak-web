<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use App\Models\GuestClient;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        // ゲストクライアントのアクセス制御
        if (!Auth::guard('guest_client')->guest() && $request->route()->parameter('request_id')) {
            $request_id = $request->route()->parameter('request_id');
            $guest_client_email = Auth::guard('guest_client')->user()->email;
            $allowed_request_ids = GuestClient::where('email', $guest_client_email)->pluck('request_id');

            if (!$allowed_request_ids->contains($request_id)) {
                return abort(403, 'Unauthorized action.');
            }
        }

        $this->authenticate($request, $guards);

        return $next($request);
    }
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}

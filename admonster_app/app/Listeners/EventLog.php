<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

use Monolog\Handler\StreamHandler; // 単一のログファイルを出力したい場合
use Monolog\Handler\RotatingFileHandler; // 日付でログファイルをローテートしたい場合

use Auth;

class EventLog
{
    protected $request;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param Authenticated $event
     * @return void
     */
    public function handle(Authenticated $event)
    {

        $user_type = 1;//1: 通常ユーザー、2:クライアント
        if (Auth::guard('guest_client')->user()) {
            $user_type = 2;
        }

        $requestMessage = [
            "channel" => config('app.env'),
            "level_name" => "INFO",
            "user_id" => $user_type == 1 ? $this->request->user()->id : Auth::guard('guest_client')->user()->id,
            "ip" => $this->request->ip(),
            "request_url" => $this->request->path(),
            "request_type" => $this->request->method(),
            "post_data" => $this->request->all() == [] ? null : $this->request->all(),
            "user_agent" => $this->request->userAgent(),
            "user_type" => $user_type,
            "level" => config('app.log_level'),
            "extra" => []
        ];
        Log::channel('event')->info('[EVENT]', $requestMessage);
    }
}

<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Fideloper\Proxy\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Amazon AWSや他の「クラウド」ロードバランサプロバイダを使用している場合は、
     * 実際のバランサのIPアドレスは分かりません。
     * このような場合、全プロキシを信用するために、*を使います。
     * https://readouble.com/laravel/5.7/ja/requests.html#configuring-trusted-proxies
     *
     * @var array|string
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}

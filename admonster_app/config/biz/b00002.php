<?php
// 経費申請業務用config

if (env('APP_ENV') == 'production') {
    // 本番環境用
    return [
        'clerk_email' => 'keihi@huihai-info.com',
    ];
} elseif (env('APP_ENV') == 'staging') {
    // ステージング環境用
    return [
        'clerk_email' => 'admonster-dev@adpro-inc.co.jp',
    ];
} else {
    // 上記以外の環境用
    return [
        'clerk_email' => 'admonster-dev@adpro-inc.co.jp',
    ];
}

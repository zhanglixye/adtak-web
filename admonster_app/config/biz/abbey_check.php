<?php
// Abbeyチェック用config

if (env('APP_ENV') == 'production') {
    // 本番環境用
    return [
        'contact_mail_receiver' => 'shuang-cui@dac.co.jp,weiwei-ding@dac.co.jp,yamasaki@dac.co.jp',
    ];
} elseif (env('APP_ENV') == 'staging') {
    // ステージング環境用
    return [
        'contact_mail_receiver' => 'admonster-dev@adpro-inc.co.jp',
    ];
} else {
    // 上記以外の環境用
    return [
        'contact_mail_receiver' => 'admonster-dev@adpro-inc.co.jp',
    ];
}

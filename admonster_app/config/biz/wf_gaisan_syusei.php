<?php
// WF概算修正業務用config

if (env('APP_ENV') == 'production') {
    // 本番環境用
    return [
        'input_ppt_info' => [
            'target_mail_receiver_1' => 'fb-kakutei@adpro-inc.co.jp',
            'target_mail_receiver_2' => 'tw_kakutei@adpro-inc.co.jp',
        ],
        'changchun_biz_email' => 'huihai_keijo@dac.co.jp',
    ];
} elseif (env('APP_ENV') == 'staging') {
    // ステージング環境用
    return [
        'input_ppt_info' => [
            'target_mail_receiver_1' => 'yutaka-sakai@dac.co.jp',
            'target_mail_receiver_2' => 'yutaka-sakai@dac.co.jp',
        ],
        'changchun_biz_email' => 'yutaka-sakai@dac.co.jp',
    ];
} else {
    // 上記以外の環境用
    return [
        'input_ppt_info' => [
            'target_mail_receiver_1' => 'yutaka-sakai@dac.co.jp',
            'target_mail_receiver_2' => 'yutaka-sakai@dac.co.jp',
        ],
        'changchun_biz_email' => 'yutaka-sakai@dac.co.jp',
    ];
}

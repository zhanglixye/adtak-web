<?php
// Abbey check業務用config

return [
    'DEFAULT_CONTENT_RESULTS' => [
        'type' => -1,
        'comment' => '',
        'mail_id' => []
    ],
    'MAIL_SETTING' => [
        'contact_mail_receiver' => env('APP_ENV') == 'production'? 'shuang-cui@dac.co.jp,weiwei-ding@dac.co.jp,yamasaki@dac.co.jp': 'support@kaiwait.com',
    ]
];

<?php

return [
    'detail' => [
        'title' => '進捗状況',
    ],
    'progress' => [
        'block_title' => [
            'request' => '依頼全体の進捗率',
            'each_steps' => '作業別の進捗状況',
        ],
    ],
    'process' => [
        'import' => '受付',
        'allocation' => '手配',
        'work' => '作業',
        'approval' => '検証',
        'delivery' => '納品',
    ],
    'process_in_progress' => [
        'import' => '受付処理中',
        'allocation' => '手配中',
        'work' => '作業進行中',
        'approval' => '検証中',
        'delivery' => '納品処理中',
    ],
    'process_completed' => [
        'import' => '受付完了',
        'allocation' => '手配完了',
        'work' => '作業完了',
        'approval' => '検証完了',
        'delivery' => '納品完了',
    ],
    'completed_at' => '作業日',
    'appendices' => [
        'block_title' => '関連情報',
        'related_mail' => '関連メール',
        'additional_info' => '補足情報',
    ],
    'request_log_types' => [
        \Config::get('const.REQUEST_LOG_TYPE.IMPORT_COMPLETED')  => [
            'title' => '受付完了',
            'content_text' => '依頼を受領しました',
        ],
        \Config::get('const.REQUEST_LOG_TYPE.ALL_COMPLETED')  => [
            'title' => '納品完了',
            'content_text' => '依頼の納品が完了しました',
        ],
    ],
];

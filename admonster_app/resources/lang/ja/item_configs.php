<?php

return [
    'name' => '項目名',
    'result_type' => [
        'label' => '結果',
        \Config::get('const.PREFIX').\Config::get('const.TASK_RESULT_TYPE.DONE') => '正常終了',
        \Config::get('const.PREFIX').\Config::get('const.TASK_RESULT_TYPE.CONTACT') => '問い合わせ',
        \Config::get('const.PREFIX').\Config::get('const.TASK_RESULT_TYPE.RETURN') => '戻り',
        \Config::get('const.PREFIX').\Config::get('const.TASK_RESULT_TYPE.CANCEL') => '中止',
        \Config::get('const.PREFIX').\Config::get('const.TASK_RESULT_TYPE.HOLD') => '保留',
    ],
    'comment' => [
        'label' => 'コメント',
    ],
    'next_step' => [
        'label' => '次作業',
    ],
    'mail' => [
        'label' => 'メール',
        'message' => 'メールの内容を確認する',
    ],
    'c00801' => [
        'unset' => '未設定',
        'open_the_file_preview' => '※クリックでプレビュー表示',
        'supported_extension' => '対応拡張子',
        'parenthesis' => [
            'open' => '（',
            'close' => '）',
        ],
    ],
];

<?php

return [
    'types' => [
        \Config::get('const.REQUEST_LOG_TYPE.IMPORT_COMPLETED')  => '取込完了',
        \Config::get('const.REQUEST_LOG_TYPE.ALLOCATION_COMPLETED')  => '割振完了',
        \Config::get('const.REQUEST_LOG_TYPE.ALLOCATION_CHANGED')  => '割振変更',
        \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_NORMALLY')  => 'タスク完了',
        \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_WITH_UNCLEAR_POINT')  => 'タスク不明処理（完了）',
        \Config::get('const.REQUEST_LOG_TYPE.TASK_HOLDED_WITH_UNCLEAR_POINT')  => 'タスク不明処理（保留）',
        \Config::get('const.REQUEST_LOG_TYPE.TASK_HOLDED_WITH_SOME_REASON')  => 'タスク保留処理',
        \Config::get('const.REQUEST_LOG_TYPE.ALL_TASKS_COMPLETED')  => '作業完了',
        \Config::get('const.REQUEST_LOG_TYPE.APPROVAL_REJECTED')  => '承認却下',
        \Config::get('const.REQUEST_LOG_TYPE.APPROVAL_ABORTED')  => '承認中止',
        \Config::get('const.REQUEST_LOG_TYPE.APPROVAL_COMPLETED')  => '承認完了',
        \Config::get('const.REQUEST_LOG_TYPE.DELIVERY_COMPLETED')  => '納品完了',
        \Config::get('const.REQUEST_LOG_TYPE.ALL_COMPLETED')  => '完了',
        \Config::get('const.REQUEST_LOG_TYPE.STEPS_RETURNED')  => '作業の戻し',
        \Config::get('const.REQUEST_LOG_TYPE.REQUEST_EXCEPTED')  => '依頼を除外',
        \Config::get('const.REQUEST_LOG_TYPE.REQUEST_EXCEPTION_CANCELED')  => '除外を取り消し',
        \Config::get('const.REQUEST_LOG_TYPE.REQUEST_HOLDED')  => '依頼保留',
        \Config::get('const.REQUEST_LOG_TYPE.REQUEST_HOLD_RELEASED')  => '依頼保留解除',
        \Config::get('const.REQUEST_LOG_TYPE.ADDITIONAL_INFO_CREATED')  => '補足情報追加',
        \Config::get('const.REQUEST_LOG_TYPE.ADDITIONAL_INFO_UPDATED')  => '補足情報編集',
        \Config::get('const.REQUEST_LOG_TYPE.ADDITIONAL_INFO_DELETED')  => '補足情報削除',
    ],
    'link_btn_text' => [
        'to_allocation_edit' => '割振画面へ',
        'to_works' => '進捗一覧へ',
        'to_approval_edit' => '承認画面へ',
        'to_delivery_detail' => '納品画面へ',
        'disabled_link_for_old_data' => '旧データのためリンクが無効です',
    ]
];

<?php

return [
    'list' => [
        'title' => '納品一覧',
        'status' => [
            'none' => '未納品',
            'done' => '納品済',
            'before' => '納品予定',
            'cancel' => 'キャンセル',
        ],
        'download_delivery_files' => '納品ファイルのダウンロード',
        'message' => [
            'limit_DL_size' => ' 一度にダウンロードできるのは:limit_sizeまでです。',
            'can_not_DL_not_delivery_data' => '納品データが登録されていないため、ダウンロードできません',
            'cannot_be_downloaded_due_to_work_without_delivery' => '納品無しの作業のため、ダウンロードできません',
            'cannot_download_multiple_delivery_data' => '納品データが登録されていない、<br>または、納品無しの作業が選択されているため、<br>ダウンロードできません（:number件）'
        ],
        'worker_detail_confirm' => '作業者の詳細を確認',
    ],
    'detail' => [
        'title' => '納品詳細',
        'message' => [
            'redelivery_1' => '再納品しますか？',
            'redelivery_2_1' => '！確認',
            'redelivery_2_2' => '※ファイルを納品する場合・・・既にファイルが存在すると上書きされます',
            'redelivery_2_3' => '※メールを納品する場合・・・同じ宛先に再送します',
            'redelivery_3_1' => '！最終確認',
            'redelivery_3_2' => '※納品後は後戻りはできません！！',
            'redelivery_3_3' => '※本当に再納品してもよろしいですか？',
        ],
        'redelivery' => '再納品',
    ],
    'edit' => [
        'title' => '納品',
        'overview' => [
            'title' => '以下:user_nameさんの作業結果を承認し納品します。',
            'task_result' => 'ユーザー実入力データ（作業画面）',
            'download' => 'ユーザー実入力データ（JSON）',
        ],
        'setting' => [
            'assign_date_cancel' => 'キャンセルする',
        ],
    ],
    'info_data' => '納品データ',
];

<?php

return [
    'list' => [
        'title' => '案件一覧',
        'status' => [
            'active' => '有効',
            'inactive' => '無効'
        ],
        'to_active' => '有効にする',
        'to_inactive' => '無効にする',
        'dl_order_file' => '案件ファイルのダウンロード',
        'changed_permission' => '権限が変更されています。<br>画面を再読み込みしてください。'
    ],
    'search_condition' => [
        'status' => [
            'all' => 'すべて',
            'active' => '有効',
            'inactive' => '無効',
        ],
    ],
    'dialog' => [
        'status' => [
            'reference_permission_only_granted_order_status_unchangable' => '※参照権限のみ付与されている案件のステータスは変更されません。',
            'to_active' => [
                'text' => '有効に切り替えます。よろしいですか。',
            ],
            'to_inactive' => [
                'text' => '無効に切り替えます。よろしいですか。',
            ],
        ],
        'order_admin'=>[
            'title' => '案件管理者を追加',
            'search' => '検索',
        ],
        'order_sharer'=>[
            'title' => '案件共有者を追加',
            'search' => '検索',
        ]
    ],
    'order_operator' => '案件担当者',
    'order_admin_delete_warning' => '※管理者を全員削除すると案件の操作が出来なくなります',
    'order_sharer' => '共有者',

    'setting' => [
        'title' => '案件設定',
        'order_name' => '案件名',
        'link_edit_item_name' => '項目名を編集する',
        'error' => [
            'no_order_name' => '件名がありません',
            'limit_order_name' => '件名は:number文字までです'
        ],
        'message' => [
            'is_order_inactive' => '案件のステータスが無効になっています。',
            'save' => '保存しますか？',
        ],
        'item' => [
            'subtitle' => '項目設定',
            'item' => '項目名',
            'current_item_name' => '現在の表示名',
            'new_item_name' => '新しい表示名',
            'text_hint' => '未入力は変更しません',
            'copy_the_current_display_name' => '現在の表示名のコピー',
            'error' => [
                'limit_display_name' => ':number文字までです',
            ],
        ],
        'custom_status' => [
            'title' => 'カスタムステータス設定',
            'message' => [
                'status_used_in_order_detail' => '案件明細で使用されているステータスです。',
                'deleting_after_unselected' => '削除後は未選択になります。',
                'delete' => '削除しますか？',
                'last_confirmation' => '！最終確認',
                'delete_last_confirmation' => '※本当に削除してもよろしいですか？',
            ],
            'dialog' => [
                'add_custom_status' => 'カスタムステータスの追加',
                'edit_custom_status' => 'カスタムステータスの編集',
                'custom_status_name' => 'カスタムステータス名',
                'attribute_setting' => '属性の設定',
                'error' => [
                    'no_text' => '入力がありません',
                    'limit_text' => ':number文字までです'
                ]
            ]
        ]
    ],
    'status' => 'ステータス',
    'order_detail_name' => \Config::get('const.APP_NAME').'件名',
];

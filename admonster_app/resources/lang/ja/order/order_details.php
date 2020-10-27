<?php

return [
    'order_info' => [
        'order' => [
            'name' => '案件名'
        ],
    ],
    'list' => [
        'title' => '案件明細一覧',
        'status' => [
            'active' => '有効',
            'inactive' => '無効'
        ],
        'create_order_detail' => '案件明細の作成',
        'create_order_detail_from_a_file' => 'ファイルから案件明細を取込む',
        'to_active' => '有効にする',
        'to_inactive' => '無効にする',
        'download_order_details' => '案件明細のダウンロード',
        'bulk_create_request' => '一括依頼作成',
        'order_setting' => '案件設定',
    ],
    'search_condition' => [
        'display' =>[
            'label' => '表示名',
            'no_data' => '表示名がありません'
        ],
        'custom_status' => [
            'status' => [
                'label' => 'カスタムステータス',
                'no_data' => 'カスタムステータスがありません'
            ],
            'attribute' => [
                'label' => '属性',
            ],
        ],
        'status' => [
            'all' => 'すべて',
            'active' => '有効',
            'inactive' => '無効',
        ],
    ],
    'dialog' => [
        'status' => [
            'to_active' => [
                'text' => '有効に切り替えます。よろしいですか。',
            ],
            'to_inactive' => [
                'text' => '無効に切り替えます。よろしいですか。',
            ],
        ],
        'business_selection' => [
            'title' => '業務選択',
            'business' => '業務',
            'business_select_remark' => '※選択した業務の依頼を作成することができます',
            'no_data_text' => '選択可能な業務はありません',
        ],
        'request_creation' => [
            'title' => '依頼作成',
            'mail_subject' => '件名',
            'success' => '依頼を作成しました。<br>画面を再読み込みします。',
            'failure' => '依頼作成に失敗しました。',
        ],
        'request_bulk_creation' => [
            'title' => '一括依頼作成',
            'mail_subject' => '件名',
            'success' => '依頼を一括作成しました。',
            'failure' => '一括依頼作成に失敗しました。',
            'confirm' => '選択した案件明細の依頼を一括作成します。',
        ],
        'setting_display_format' => [
            'display_type_edit' => '表示形式の編集',
            'display_format' => 'の表示形式',
            'data_type' => 'データ形式： :item_type',
            'here_display_type_unsettable' => 'こちらの表示形式は設定できません',
            'item_type_'.\Config::get('const.ITEM_TYPE.STRING.ID') => [
                'type_name' => 'テキスト'
            ],
            'item_type_'.\Config::get('const.ITEM_TYPE.NUM.ID') => [
                'type_name' => '数値',
                \Config::get('const.PREFIX').\Config::get('const.DISPLAY_FORMAT.NUM_TREE_DIGITS_COMMA_DELIMITED') => '三桁カンマ区切り'
            ],
            'item_type_'.\Config::get('const.ITEM_TYPE.DATE.ID') => [
                'type_name' => '日付'
            ],
            'item_type_'.\Config::get('const.ITEM_TYPE.URL.ID') => [
                'type_name' => 'URL'
            ],
        ]
    ],
    'show' => [
        'title' => '案件明細詳細',
        'create' => '作成',
        'edit' => '編集',
        'subject' => \Config::get('const.APP_NAME').'件名',
        'mail' => [
            'body' => [
                'here_mail_body_create_please' => 'ここにメール本文を作成してください',
            ],
        ],
        'request_list' => [
            'no_data_text' => '当明細に紐づく依頼はありません',
        ],
        'information_component_management' => [
            'header' => [
                'switch' => '切り替え',
                'grow' => '拡大',
                'shrink_to_right' => '右側に縮小',
                'shrink_to_left' => '左側に縮小',
                'edit' => '編集',
                'other' => 'その他',
                'create_request' => '依頼作成',
                'create_mail' => '新規メール作成',
            ],
            'order_detail_info' => [
                'title' => '案件情報',
                'subject' => \Config::get('const.APP_NAME').'件名',
                'custom_status' => 'カスタムステータス',
                'item' => '項目',
                'no_data' => 'データがありません',
                'mail_created' => 'メール作成',
                'number_data_entry_rule' => [
                    'error_message' => [
                        'contain_text' => '文字が含まれています',
                        'minus_included_multiple' => 'マイナスが複数含まれています',
                        'minus_position_is_different' => 'マイナスの位置が合っていません',
                        'not_input_number' => '数値が入力されていません',
                        'less_than_minimum_value' => '最小値を下回っています',
                        'exceeded_maximum_value' => '最大値を超えています',
                        'max_than_value_is_large' => '上限より値が大きくなっています',
                        'lower_limit_than_value_is_small' => '下限より値が小さくなっています',
                    ]
                ],
                'text_data_entry_rule' => [
                    'error_message' => [
                        'no_order_detail_name' => '件名がありません',
                        'limit_order_detail_name' => '件名は:number文字までです',
                        'limit_display_name' => ':number文字までです',
                    ],
                ],
                'is_order_inactive' => '案件のステータスが無効になっています。',
                'is_order_details_inactive' => '案件明細のステータスが無効になっています。',
                'save' => '保存しますか？',
                'has_creating_newly' => '新規作成しますか？',
            ],
            'related_mail' => [
                'title' => '関連メール',
                'import' => '関連メール取込',
                'link_text_import' => '関連メールを取込む',
                'attachments' => '添付ファイル',
                'other_attachment_number' => '他:count件の添付ファイル',
                'failed_to_deletion' => '削除に失敗しました。',
                'create_dialog' => [
                    'title' => '関連メールの取込方法',
                    'steps' => [
                        '①「送付先メールアドレスを取得」ボタンをクリック',
                        '②生成されたメールアドレス宛に関連メールを送信',
                    ],
                    'annotation' => '※送信後まもなく当画面に反映されます。',
                    'create_btn' => '送付先メールアドレスを取得',
                    'completed' => [
                        'expiration_date' => '※メールアドレスの有効期限は10分間です',
                        'client_display' => '※このメールアドレスから取込んだメールはクライアントページに表示',
                        'task_display' => '※このメールアドレスから取込んだメールは作業ページに表示',
                    ],
                    'switcher' => [
                        'on' => 'されます',
                        'off' => 'されません',
                    ],
                ],
                'delete_dialog' => [
                    'confirm' => '削除します。よろしいですか？',
                ]
            ],
            'advertisement_material' => [
                'title' => '広告素材',
            ],
            'additional_info' => [
                'title' => '補足情報',
            ],
            'request_info' => [
                'title' => '依頼情報',
            ],
            'editing' => [
                'title' => '編集中',
            ],
            'create_mail' => [
                'title' => 'メール作成',
                'discard' => '破棄',
                'to_not_input' => 'Toが未入力です',
                'send_mail' => '送信されました',
                'no_send_mail' => '送信できませんでした',
            ],
            'create_request' => [
                'title' => '依頼作成',
            ],
        ],
    ]
];

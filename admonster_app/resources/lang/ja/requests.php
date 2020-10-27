<?php

return [
    'processes' => [
        'import' => '取込',
        'allocation' => '割振',
        'work' => '作業',
        'approval' => '承認',
        'delivery' => '納品',
    ],
    'list' => [
        'title' => '依頼一覧',
        'request_status' => [
            'doing' => '対応中',
            'finish' => '完了',
            'except' => '除外',
        ],
    ],
    'detail' => [
        'title' => '依頼詳細',
        'deadline' => '納期',
        'unset' => '未設定',
        'btn_except' => '対応不要にする',
        'btn_share_status' => 'ステータス共有',
        'btn_replicate' => '依頼を複製する',
        'prefix_url' => '複製元URL：',
        'created_user' => '登録者',
        'created_at' => '登録日時',
        'to_be_checked' => [
            'header' => '確認事項',
            'no_count' => '確認事項はありません',
        ],
        'status_summary' => [
            'header' => 'ステータスサマリー',
        ],
        'step_progress' => [
            'header' => '作業進捗状況',
            'btn_change_view' => '表示切替',
            'has_deleted_request_work' => '依頼の除外処理に伴い、この依頼作業は削除されました。',
            'task_result_type_contact' => '「不明あり」で処理されました。',
            'links' => [
                'to_allocation_edit' => '割振画面へ',
                'to_works' => '進捗一覧へ',
                'to_approval_edit' => '承認画面へ',
                'to_delivery_detail' => '納品画面へ',
            ],
        ],
        'revision' => 'リビジョン',
        'check_past_revisions' => '過去履歴を見る',
        'not_exist_active_data' => '有効なデータがありません',
        'past_revisions_modal' => [
            'header' => '過去リビジョン',
            'last_updated_at' => '最終更新日時',
        ],
        '_modal' => [
            'p1' => '対応不要として処理します。',
            'p2' => 'この依頼の取込データを元に、新規に同じ内容の依頼を作成します。',
            'suffix' => 'よろしいですか？',
            'description' => '以下のデータも複製する',
            'description_notes' => '(※クライアント公開設定はすべてリセットされます)',
            'related_mail' => '関連メール',
            'additional_info' => '補足情報',
            'additional_info_notes' => ' (※既存データの登録者情報はあなたに引き継がれます)',
            'replicated' => '新規依頼を作成しました。',
            'link_to_new' => '新規依頼の詳細画面へ',
            'link_to_index' => '依頼一覧へ',
            'notify' => 'データの更新に失敗しました。',
            'guest_client' => [
                'title' => 'クライアントページ提供',
                'link_to_client_page' => '提供するクライアントページ',
                'form' => [
                    'email' => [
                        'label' => '提供する宛先',
                        'placeholder' => '(複数選択可)',
                    ],
                ],
                'list' => [
                    'title' => '提供履歴',
                    'headers' => [
                        'mail_address' => 'メールアドレス',
                        'provision_date' => '提供日時',
                        'provider' => '提供者',
                    ],
                ],
            ],
        ],
        'related_mails' => [
            'title' => '関連メール',
            'import' => '関連メール取込',
            'link_text_import' => '関連メールを取込む',
            'create_dialog' => [
                'title' => '関連メールの取込方法',
                'custom_from' => '送信元メールアドレスを指定する',
                'steps' => [
                    '①クライアントページへの表示/非表示を選択（取込後も変更可）',
                    '②「送付先メールアドレスを取得」ボタンをクリック',
                    '③生成されたメールアドレス宛に関連メールを送信',
                ],
                'annotation' => '※送信後まもなく当画面に反映されます。',
                'settings' => [
                    'client_display' => 'クライアントページへの表示: ',
                    'task_display' => '作業ページへの表示: ',
                ],
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
        ],
        'additional_info' => [
            'title' => '補足情報',
            'add' => '補足情報追加',
            'link_text_add' => '補足情報を追加する',
            'auto_comments' => [
                'imported_by_file' => '当依頼はこのコメントに添付のファイルから取込まれました。',
                'confirm_at_imported_file_list' => '取込一覧で確認する',
            ],
            'notice_link' => '補足情報あり',
        ],
        'client' => [
            'is_status_open' => 'クライアントページに表示中',
            'is_status_closed' => 'クライアントページに非表示中',
            'confirm_to_open' => 'クライアントページに表示させますか？',
            'confirm_to_close' => 'クライアントページで非表示にしますか？',
            'switch_label' => 'クライアントページへの表示',
        ],
        'work' => [
            'is_status_open' => '作業ページに表示中',
            'is_status_closed' => '作業ページに非表示中',
            'confirm_to_open' => '作業ページに表示させますか？',
            'confirm_to_close' => '作業ページで非表示にしますか？',
            'switch_label' => '作業ページへの表示',
        ],
    ],
    'search_condition' => [
        'business_name'=> '業務名 / ID',
        'created_at' => '発生日',
        'deadline' => '納品期限',
        'switch_date_type' => '日付切替',
        'partial_search' => '（部分検索可）',
        'from' => ' From',
        'to' => ' To',
        'client_name' => '依頼主',
        'request_name' => '件名',
        'status' => [
            'label' =>'ステータス',
            'all' => 'すべて',
            'doing' => '対応中',
            'finished' => '完了',
            'Exclusion' => '除外'
        ],
    ],
    'client_name' => '依頼主',
    'request_name' => '件名',
    'created_at'=> '発生日',
    'deadline' => '納品期限',
    'id' => '依頼ID',
    'code' => '依頼コード',
    'count' => '件',
    'confirm' => '確認',
    'auto' => '自動',
];

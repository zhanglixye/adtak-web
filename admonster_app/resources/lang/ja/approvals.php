<?php
return [
    'list' => [
        'title' => '承認一覧',
        'h1' => '承認一覧',
        'approval_status' => [
            'none' => '未承認',
            'on' => '保留',
            'done' => '承認済',
        ],
        'is_auto' => [
            'false' => '手動',
            'true' => '自動'
        ],
        'conditions' => [
            'full_match' => '完全一致'
        ],
        'worker_detail_confirm' => '作業者の詳細を確認'
    ],
    'title' => '承認',
    'item_name' => '項目名',
    'operator' => '担当者',
    'not_immediate' => '即納しない',
    'button' => [
        'open_candidates' => '担当者一覧表示'
    ],
    'sort' => [
        'label' => 'ソート',
        'items' => [
            '0' => '完了日時'
        ]
    ],
    'dialog' => [
        'approval' => [
            'error' => [
                'selected_count' => '承認対象を一件選択してください。',
            ],
            'attention' => [
                'other_worker_editing' => '作業中のユーザが存在します。作業中タスクは無効になりますが承認しますか？'
            ],
            'check_immediate' => '承認後納品されます。',
            'check_list' => [
                '承認したら戻すことはできません。',
            ],
        ],
        'delivery' => [
            'text' => '表示している内容で納品します。よろしいですか？',
        ],
        'reject' => [
            'text' => 'さんの作業データをNGデータとして処理しました。'
        ],
        'order_work' => [
            'worker' => [
                'label' => '担当者'
            ],
            'reason' => [
                'label' => '担当者へのコメント'
            ],
            'text' => 'さんに再作業を依頼しますがよろしいですか？'
        ],
        'back' => [
            'text' => '作業途中ですが、このページから移動してもよろしいですか？'
        ],
        'allocate' => [
            'confirm' => 'さんに割振ります。よろしいですか？',
            'finish' => '割振りました',
        ],
    ],
    'alert' => [
        'order_work' => [
            'text' => '再作業を依頼しました。'
        ]
    ],
    'window_open' => [
        'biz' => '作業画面を確認'
    ],
    'table_custom' => [
        'width_title' => '列幅調整'
    ],
    'tooltip_text' => [
        'fix_to_head' => '先頭にピン留め',
        'again_work' => '再作業を依頼',
    ],
    'file_preview' => [
        'title' => '比較プレビュー',
        'read_error' => '読み込みエラー',
        'next' => '次の',
        'show_count' => '件を表示',
        'support_extension_list' => 'ファイルプレビュー対応拡張子',
        'compare_same_file_name' => '同一ファイル名で比較',
        'here_supported_list' => '対応拡張子の一覧はこちら'
    ]
];

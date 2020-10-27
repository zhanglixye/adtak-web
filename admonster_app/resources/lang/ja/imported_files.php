<?php

return [
    'index' => [
        'title' => 'ファイル取込',
        'setting_subtitle' => '取込設定',
        'subtitle' => '読込内容確認',
    ],
    'search_condition' => [
        'status' => [
            'all' => '全て',
            'doing' => '処理中',
            'finished' => '処理完了',
        ],
    ],
    'import' => '新規取込',
    'latest_imported_file' => '最近取込んだファイル',
    'import_dialog' => [
        'header' => '新規取込',
        'form' => [
            'select_business' => [
                'radio_group_label' => '業務の依頼として取り込む',
                'label' => '業務',
                'placeholder' => '対象業務を選択してください',
            ],
            'select_order' => [
                'radio_group_label' => '案件として取り込む',
                'label' => '案件名',
                'hint' => '未入力時はファイル名が案件名になります'
            ]
        ],
        'button' => [
            'import' => '読込',
        ],
        'validation' => [
            'business' => '業務を選択してください',
            'file' => 'ファイルを選択してください'
        ],
    ],
    'order_imported_setting' => [
        'subject' => \Config::get('const.APP_NAME').'件名',
        'search_key' => '検索キー',
        'system_on_by_search_items' => '※システム上で検索する項目',
        'subject_considerations' => [
            'length_limit' => '※作成された件名は256文字を超えると切り捨てられます。',
            'please_selected_subject' => '※件名を選択してください'
        ],
        'no_display_name_error' => '表示名を入力して下さい',
        'import_considerations' => '※システムが正しく取り込めない場合があります（関数の結果など）',
        'items' => [
            'column' => '列',
            'item_name' =>  '項目名',
            'display_name' => '表示名',
            'data_format' => 'データの形式',
            'data_input_rule' => 'データの入力ルール'
        ],
        'setting' => '設定する',
        'url_is_unsettable' => 'URLは設定できません',
        'text_data_entry_rule' => [
            'subject' => 'テキスト',
            'upper_limit' => '上限',
            'hint' => '上限文字数は:max_lengthです',
            'text' => '文字',
            'input_limitation' => '入力制限',
            'no_setting' => '設定しない',
            'setting' => '設定する',
            'required' => '必須',
            'input_text_format' => [
                \Config::get('const.PREFIX').\Config::get('const.INPUT_RULE.TEXT.HALFWIDTH_ALPHANUMERIC_SYMBOL') => '半角英数字記号',
                \Config::get('const.PREFIX').\Config::get('const.INPUT_RULE.TEXT.FULLWIDTH_ALPHANUMERIC_SYMBOL') => '全角英数字記号',
                \Config::get('const.PREFIX').\Config::get('const.INPUT_RULE.TEXT.FULLWIDTH_HIRAGANA_KATAKANA') => '全角かなカナ',
            ]
        ],
        'date_data_entry_rule' => [
            'subject' => '日付',
            'today' => '今日',
            'yesterday' => '昨日',
            'currentMonth' => '今月',
            'lastMonth' => '先月',
            'currentYear' => '今年',
            'custom' => 'カスタム',
            'required' => '必須',
        ],
        'number_data_entry_rule' => [
            'subject' => '数値',
            'rule_preference' => 'ルール設定',
            'display_format' => '表示形式',
            'upper_limit' => '上限',
            'lower_limit' => '下限',
            'required' => '必須',
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
        'url_data_entry_rule' => 'URL'
    ],
    'order_confirm' => [
        'order_name' => '案件名：',
        'display_name' => '表示名',
    ],
    'error_item_list_dialog' => [
        'header' => 'エラー項目の内訳情報',
    ],
    'error_messages' => [
        'get_business_list' => '取込対象の業務リストを取得できませんでした',
        'delete_file' => 'ファイルの削除に失敗しました',
    ],
    'file_name' => 'ファイル名',
    'import_config' => '取込設定',
    'toggle_vertically_and_horizontally' => '縦横表示切り替え',
    'header_row_num' => '表頭行No.',
    'data_start_row_num' => 'データ開始行No.',
    'import_target_request_cnt' => '取込む依頼数',
    'import_target_order_cnt' => '取り込む案件明細数',
    'points' => '箇所',
    'line' => '行目',
    'row' => '行',
    'item_name_for_system' => 'システム項目名',
    'data_row_caption' => 'データとして取込みません',
    'no_items' => '最低でもA1に項目が1つ必要です<br>前の画面に遷移します',
    'upper_limit_items' => '項目数が:numberを超える案件は取り込めません<br>前の画面に遷移します',
    'no_data' => 'データは最低1件は必要です<br>前の画面に遷移します',
    'item_name_has_a_line_break' => '項目名に改行が含まれているため取り込めません<br>前の画面に遷移します',
    'duplicate_item_name' => '項目名が重複しているため取り込めません<br>前の画面に遷移します',
    'error' => 'エラー',
    'error_cnt' => 'エラー数',
    'no_errors' => 'エラーはありません',
    'see_only_errors' => 'エラー箇所だけを見る',
    'see_all' => '全体を見る',
    'item_types' => [
        \Config::get('const.ITEM_TYPE.STRING.ID') => 'テキスト',
        \Config::get('const.ITEM_TYPE.NUM.ID') => '数値',
        \Config::get('const.ITEM_TYPE.DATE.ID') => '日付',
        \Config::get('const.ITEM_TYPE.AMOUNT.ID') => '金額',
        \Config::get('const.ITEM_TYPE.URL.ID') => 'URL',
    ]
];

<?php
/*
Abbeyチェック業務用の言語テキスト

[呼び出し方法]

'検索'を呼び出す場合
@lang('biz/abbey_check.work.item06')

bladeでの使用例
<button>@lang('biz/abbey_check.work.item06')</button>

blade内のjsで使う場合は、
<?php echo __('biz/abbey_check.work.item06'); ?>
*/

// [TODO]: keyをどこか共通の場所に定義して読み込むようにする
$prefix = 'prefix';
$boolean_form = [
    ['key' => $prefix.'0', 'value' => 'いいえ'],
    ['key' => $prefix.'1', 'value' => 'はい'],
];

return [
    'common' => [
        'project_name' => 'ad-monster',
        'link_to_index' => '一覧に戻る',
        'btn_submit' => '処理する',
        'status_done' => '処理済み',
        'loading' => '読み込み中',
        '_modal' => [
            'btn_submit' => 'OK',
            'btn_cancel' => 'キャンセル',
        ],
        'request_mail_box_title' => '依頼メール',
        'mail' => [
            'subject' => '件名',
            'body' => '本文',
            'attachment_file' => '添付ファイル',
        ],
        'download' => 'ダウンロード',
        'select_default_text' => '選択してください',
        'comment' => 'コメント',
        'task_result_type_text' => [
            $prefix.\Config::get('const.TASK_RESULT_TYPE.HOLD') => '保留',
            $prefix.\Config::get('const.TASK_RESULT_TYPE.CONTACT') => '不明あり',
            $prefix.\Config::get('const.TASK_RESULT_TYPE.DONE') => '結果報告',

        ],
        '_modal' => [
            'irregular' => [
                $prefix.\Config::get('const.TASK_RESULT_TYPE.CANCEL') => '「対応不要」として処理します。',
                $prefix.\Config::get('const.TASK_RESULT_TYPE.HOLD') => 'このタスクを保留にします。',
                $prefix.\Config::get('const.TASK_RESULT_TYPE.CONTACT') => '「不明あり」で処理します。',
                'placeholder' => [
                    'comment' => 'コメントを入力してください。',
                ],
            ],
        ],
        'irregularBtnsBox_trigger' => '通常処理以外の場合',
        'messages' => [
            'failed_to_retrieve_data' => 'データの取得に失敗しました。',
        ],
        'no_data' => 'データがありません',
    ],
    // Abbeyチェック
    'abbey_check' => [
        'file_path' => [
            'label' => '素材'
        ],
        'abbey_id' => [
            'label' => 'AbbeyID'
        ],
        'err_description' => [
            'label' => 'Abbeyチェック結果'
        ],
        'client_name' => [
            'label' => 'クライアント名',
        ],
        'err_detail' => [
            'label' => 'エラー内容'
        ],
        'check_files' => [
            'label' => '素材'
        ],
        'title' => 'Abbeyチェック',
        'is_success' => [
            'label' => 'チェック判定',
            'prefixtrue' => 'OK',
            'prefixfalse' => 'NG',
        ],
        'step' => [
            'material' => '素材',
            'result_capture' => '結果キャプチャ',
            'client_name' => 'クライアント名',
        ],
        'item_label' => [
            'file_name' => '元ファイル名',
            'check_file_name' => 'チェックファイル名',
            'pixel_number' => '天地左右',
            'file_size' => 'ファイルサイズ',
            'abbey_id' => 'AbbeyID',
            'menu' => 'メニュー名',
            'err_description' => 'Abbeyチェック結果',
            'err_detail' => 'エラー内容'
        ],
        'tooltip_message' => [
            'check_file_download' => 'チェックファイル名でダウンロード',
            'abbey_search' => 'Abbey検索画面を開く'
        ],
        '_modal' => [
            'fix' => [
                'p1' => '作業内容を確定します。',
                'p2' => 'チェック結果の確認は大丈夫ですか？',
            ],
            'unclear' => [
                'p1' => '「不明あり」で処理します。',
                'p2' => 'よろしいですか？',
            ],
        ],
        'tool_url' => 'Abbeyツールを開く',
        'all_download' => '一括DL',
    ],
    // AbbeyID検索
    'abbey_search' => [
        'h1' => 'Abbey検索画面',
        'search' => '検索ワード',
        'item_label' => [
            'specification' => '仕様',
            'purpose' => '用途',
            'abbey_id' =>  'Abbey ID',
            'pixel' => 'ピクセル(WxH)',
            'file_size' => 'ファイルサイズ',
            'file_format' => 'ファイル形式',
            'total_bit_rate' => '総ビットレート',
            'animation' => 'アニメーション',
            'target_Loudness' => 'ターゲットラウドネス値',
            'alt_text_content' => 'ALTテキスト',
            'link' => 'リンク先',
        ],
        'item_data_unit' => [
            'file_size' => [
                'KB' => 'KB以内',
                'MB' => 'MB以内'
            ]
        ],
        'material_info' => [
            'delimiter_1' => ' / ',
            'delimiter_2' => '：',
            'title' => '素材情報',
            'name' => '元ファイル名',
            'aspect_ratio' => 'ピクセル',
            'size' => 'サイズ',
        ]
    ],
];

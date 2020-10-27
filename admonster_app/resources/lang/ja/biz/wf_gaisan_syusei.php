<?php
/*
WF概算修正業務用の言語テキスト

[呼び出し方法]

'検索'を呼び出す場合
@lang('biz/wf_gaisan_syusei.work.item06')

bladeでの使用例
<button>@lang('biz/wf_gaisan_syusei.work.item06')</button>

blade内のjsで使う場合は、
<?php echo __('biz/wf_gaisan_syusei.work.item06'); ?>
*/

// [TODO]: keyをどこか共通の場所に定義して読み込むようにする
$prefix = 'prefix';
$boolean_form = [
    ['key' => $prefix.'0', 'value' => 'いいえ'],
    ['key' => $prefix.'1', 'value' => 'はい'],
];

return [
    'common' => [
        'link_to_index' => '一覧に戻る',
        // 項目名
        'item_label' => [
            'sales_person' => '営業担当',
            'agency' => '代理店名',
            'advertiser' => '広告主名',
            'campaign' => 'キャンペーン名',
            'cp' => 'CP名',
            'site' => 'サイト名',
            'menu' => 'メニュー名',
            'from' => '開始日',
            'to' => '終了日',
            'amount_01' => '申込金額',
            'amount_02' => '確定金額',
            'amount_03' => '確定金額（グロス）',
            'amount_04' => '確定金額（ネット）',
            'sns_cp' => 'SNS_CP名',
            'sales_order_line_no' => '受注明細番号',
            'implementation-fee' => '実施料金'."\n".'変更後',
        ],
        'btn_submit' => '処理する',
        'status_done' => '処理済み',
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
        'spreadsheet' => [
            'get_data_failed' => 'スプレッドシートからのデータ取得に失敗しました。',
            'get_credentials_failed' => 'スプレッドシートのデータ取得に必要な認証情報が取得できませんでした。再読み込みしても解決しない場合は業務管理者にお問い合わせください。',
        ],
        'comment' => 'コメント',
        'task_result_type_text' => [
            $prefix.\Config::get('const.TASK_RESULT_TYPE.CANCEL') => '対応不要',
            $prefix.\Config::get('const.TASK_RESULT_TYPE.HOLD') => '保留',
            $prefix.\Config::get('const.TASK_RESULT_TYPE.CONTACT') => '不明あり',

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
    ],
    // メール仕訳画面用
    'assort_mail' => [
        'h1' => 'メール仕訳',
        'step1' => [
            'step_description' => 'メールから以下の項目を入力してください',
            'step_description_sub_1' => '右図の内容だった場合はSTEP1入力不要です',
        ],
        'step2' => [
            'step_description' => '確定金額単位でJOBNOを入力を入力してください',
        ],
        'announce_text' => '不明な点はありますか？',
        'unclearBox_trigger' => '不明ありの場合はクリック',
        'unclear_box' => [
            'title' => '該当する不明点にチェック',
        ],
        'unclear_ptn_info' => [
            'title' => '下記内容は不明ありで処理',
            'ptn_list' => [
                '1' => '対応不要クライアント(アップル/P&G/Amazon/アウディ)',
                '2' => 'to代理店への確定連絡以外',
                '3' => '期間途中の確定金額',
                '4' => '件名に【CCC】※別フロー対応',
                '5' => 'キリン案件※別フロー対応',
                '6' => 'JOBNOなし※調べるor問い合わせ',
            ],
        ],
        'irregularBtnsBox_trigger' => '通常処理以外の場合',
        'item01' => [
            'label' => 'DAC営業担当',
        ],
        'item02' => [
            'label' => '代理店名',
        ],
        'item03' => [
            'label' => '広告主名',
        ],
        'item04' => [
            'label' => 'キャンペーン名',
        ],
        'item05' => [
            'label' => 'JOBNO',
        ],
        'item06' => [
            'label' => 'WF対応不要クライアント案件',
            $boolean_form[0]['key'] => $boolean_form[0]['value'],
            $boolean_form[1]['key'] => $boolean_form[1]['value'],
        ],
        'item07' => [
            'label' => 'WF対応不要クライアント案件（コメント）',
            'placeholder' => '内容を入力してください',
        ],
        'item08' => [
            'label' => 'to代理店への確定金額メールではない',
            $boolean_form[0]['key'] => $boolean_form[0]['value'],
            $boolean_form[1]['key'] => $boolean_form[1]['value'],
        ],
        'item09' => [
            'label' => 'to代理店への確定金額メールではない（コメント）',
            'placeholder' => '内容を入力してください',
        ],
        'item10' => [
            'label' => '件名に【CCC】が含まれる',
            $boolean_form[0]['key'] => $boolean_form[0]['value'],
            $boolean_form[1]['key'] => $boolean_form[1]['value'],
        ],
        'item11' => [
            'label' => '件名に【CCC】が含まれる（コメント）',
            'placeholder' => '内容を入力してください',
        ],
        'item12' => [
            'label' => '期間途中の確定金額',
            $boolean_form[0]['key'] => $boolean_form[0]['value'],
            $boolean_form[1]['key'] => $boolean_form[1]['value'],
        ],
        'item13' => [
            'label' => '期間途中の確定金額（コメント）',
            'placeholder' => '内容を入力してください',
        ],
        'item14' => [
            'label' => 'その他',
            $boolean_form[0]['key'] => $boolean_form[0]['value'],
            $boolean_form[1]['key'] => $boolean_form[1]['value'],
        ],
        'item15' => [
            'label' => 'その他（コメント）',
            'placeholder' => '内容を入力してください',
        ],
        '_modal' => [
            'fix' => [
                'p1' => '作業内容を確定します。',
                'p2' => 'JOBNOの見逃し等ありませんか？',
            ],
            'unclear' => [
                'p1' => '「不明あり」で処理します。',
                'p2' => 'よろしいですか？',
            ],
        ],
    ],
    // メール情報入力画面用
    'input_mail_info' => [
        'h1' => 'メール情報入力',
        'step1' => [
            'step_description' => '左記メールの本文より、対象JOBNOの以下の項目を入力してください',
            'step_description_sub_1' => '期間・申込金額に変更があった場合は<span class="text-underline">「最新の期間」</span>を入力する',
            'step_description_sub_2' => '引用文から判断を要する場合は、<span class="text-underline">不明あり→「本文の情報不足」</span>として処理する',
            'link_guide' => 'メール参照方法サンプル',
        ],
        'announce_text' => '不明な点はありますか？',
        'unclearBox_trigger' => '不明ありの場合はクリック',
        'unclear_box' => [
            'title' => '該当する不明点にチェック',
        ],
        'item01' => [
            'label' => 'DAC営業担当',
        ],
        'item02' => [
            'label' => '代理店名',
        ],
        'item03' => [
            'label' => '広告主名',
        ],
        'item04' => [
            'label' => 'キャンペーン名',
        ],
        'item05' => [
            'label' => '実施期間（開始日）',
        ],
        'item06' => [
            'label' => '実施期間（終了日）',
        ],
        'item07' => [
            'label' => '申込金額',
        ],
        'item08' => [
            'label' => '確定金額',
        ],
        'item09' => [
            'label' => '本文の情報不足',
            $boolean_form[0]['key'] => $boolean_form[0]['value'],
            $boolean_form[1]['key'] => $boolean_form[1]['value'],
        ],
        'item10' => [
            'label' => '本文の情報不足（コメント）',
            'placeholder' => '内容を入力してください',
        ],
        'item11' => [
            'label' => '項目が判断できない',
            $boolean_form[0]['key'] => $boolean_form[0]['value'],
            $boolean_form[1]['key'] => $boolean_form[1]['value'],
        ],
        'item12' => [
            'label' => '項目が判断できない（コメント）',
            'placeholder' => '内容を入力してください',
        ],
        'item13' => [
            'label' => 'その他',
            $boolean_form[0]['key'] => $boolean_form[0]['value'],
            $boolean_form[1]['key'] => $boolean_form[1]['value'],
        ],
        'item14' => [
            'label' => 'その他（コメント）',
            'placeholder' => '内容を入力してください',
        ],
        '_modal' => [
            'fix' => [
                'p1' => '作業内容を確定します。',
                'p2' => 'よろしいですか？',
            ],
            'unclear' => [
                'p1' => '「不明あり」で処理します。',
                'p2' => 'よろしいですか？',
            ],
        ],
    ],
    // SAS情報入力画面用
    'input_sas_info' => [
        'h1' => 'SAS情報入力',
        'step1' => [
            'step_description' => 'SASの「受注明細詳細画面」から入力してください',
            'step_description_sub' => '受注明細番号が未入力の場合は、<span class="text-underline">不明あり→「受注明細番号がない」</span>として処理する',
            'guide_breadcrumb' => 'SASトップページ＞受注明細を入力して検索＞サイトメニュー名のリンクをクリック＞受注明細「参照」をクリック',
            'link_guide' => '受注明細詳細画面サンプル',
        ],
        'step2' => [
            'step_description' => 'SASの「キャンペーン詳細画面」から入力してください',
            'step_description_sub' => '受注明細が複数ある場合はすべての受注明細番号を入力する',
            'guide_breadcrumb' => 'SASトップページ＞受注明細を入力して検索＞サイトメニュー名のリンクをクリック＞受注明細「申請」をクリック',
            'link_guide' => 'キャンペーン詳細画面サンプル',
        ],
        'announce_text' => '不明な点はありますか？',
        'unclearBox_trigger' => '不明ありの場合はクリック',
        'unclear_box' => [
            'title' => '該当する不明点にチェック',
        ],
        'item01' => [
            'label' => 'DAC営業担当',
        ],
        'item02' => [
            'label' => '代理店名',
        ],
        'item03' => [
            'label' => '広告主名',
        ],
        'item04' => [
            'label' => 'キャンペーン名',
        ],
        'item05' => [
            'label' => 'サイト名',
        ],
        'item06' => [
            'label' => 'メニュー名',
        ],
        'item07' => [
            'label' => '期間（開始日）',
        ],
        'item08' => [
            'label' => '期間（終了日）',
        ],
        'item09' => [
            'label' => '代理店手数料率',
        ],
        'item10' => [
            'label' => '原価率',
        ],
        'item11' => [
            'label' => '実施料金',
        ],
        'item12' => [
            'label' => '代理店手数料',
        ],
        'item13' => [
            'label' => '原価',
        ],
        'item14' => [
            'label' => '受注明細番号①',
        ],
        'item15' => [
            'label' => '受注明細番号②',
        ],
        'item16' => [
            'label' => '受注明細番号③',
        ],
        'item17' => [
            'label' => '入力の仕方がわからない',
            $boolean_form[0]['key'] => $boolean_form[0]['value'],
            $boolean_form[1]['key'] => $boolean_form[1]['value'],
        ],
        'item18' => [
            'label' => '入力の仕方がわからない（コメント）',
            'placeholder' => '内容を入力してください',
        ],
        'item19' => [
            'label' => '受注明細番号がない',
            $boolean_form[0]['key'] => $boolean_form[0]['value'],
            $boolean_form[1]['key'] => $boolean_form[1]['value'],
        ],
        'item20' => [
            'label' => '受注明細番号がない（コメント）',
            'placeholder' => '内容を入力してください',
        ],
        'item21' => [
            'label' => 'その他',
            $boolean_form[0]['key'] => $boolean_form[0]['value'],
            $boolean_form[1]['key'] => $boolean_form[1]['value'],
        ],
        'item22' => [
            'label' => 'その他（コメント）',
            'placeholder' => '内容を入力してください',
        ],
        '_modal' => [
            'fix' => [
                'p1' => '作業内容を確定します。',
                'p2' => 'よろしいですか？',
            ],
            'unclear' => [
                'p1' => '「不明あり」で処理します。',
                'p2' => 'よろしいですか？',
            ],
        ],
    ],
    // パワポ特定画面用
    'identify_ppt' => [
        'h1' => 'パワポ特定',
        'step1' => [
            'step_description' => ' 画面左側にあるメールリストから、下記条件のもと、対象メールを選択（クリック）してください',
            'target_mail_condition' => [
                'element' => [
                    '01' => '件名',
                    '02' => '受信日時',
                ],
                'content' => [
                    '02' => '案件終了日時以降',
                ],
            ],
        ],
        'step2' => [
            'step_description' => '対象JOBNOのパワポを選択してください',
            'step_description_sub_1' => '添付がない場合：「パワポなし」を選択',
            'step_description_sub_2' => 'パワポの添付が複数ある場合：引用文の中に申込受領のキャンペーン名と一致する文言がある方など判断のうえ特定',
        ],
        'step3' => [
            'step_description' => 'メール本文内より対象JOBNOの以下の項目を入力してください',
        ],
        'item02' => [
            'label' => '対象メール',
            'hint' => 'STEP1で選択したメールの添付パワポ',
        ],
        'item03' => [
            'label' => '対象パワポ',
            'label' => 'パワポなし',
        ],
        'item04' => [
            'label' => '申込金額（グロス）',
        ],
        'item05' => [
            'label' => '確定金額（グロス）',
        ],
        'item06' => [
            'label' => '確定金額（ネット）',
        ],
        'announce_text' => '不明な点はありますか？',
        'unclearBox_trigger' => '不明ありの場合はクリック',
        'unclear_box' => [
            'title' => '該当する不明点にチェック',
        ],
        'item07' => [
            'label' => 'その他',
            $boolean_form[0]['key'] => $boolean_form[0]['value'],
            $boolean_form[1]['key'] => $boolean_form[1]['value'],
        ],
        'item08' => [
            'label' => 'その他（コメント）',
            'placeholder' => '内容を入力してください',
        ],
        '_modal' => [
            'fix' => [
                'p1' => '作業内容を確定します。',
                'p2' => 'よろしいですか？',
            ],
            'unclear' => [
                'p1' => '「不明あり」で処理します。',
                'p2' => 'よろしいですか？',
            ],
        ],
        'no_mails' => '対象のメールが存在しません',
    ],
    // パワポ情報入力画面用
    'input_ppt_info' => [
        'h1' => 'パワポ情報入力',
        'target_file' => '対象ファイル',
        'no_target_file' => '対象ファイルがありません',
        'link_guide' => 'パワポ（管理画面キャプチャ）サンプル',
        'step1' => [
            'step_description' => '対象ファイルをダウンロードし、パワポの見方を参照して下記情報を入力してください',
            'step_description_sub' => '<span>【条件】</span><span>送信日時：対象案件の終了日よりも「後」<br>件名：冒頭に「確定金額」<br>本文：名乗りが「○○○○サポートチーム」</span>',
        ],
        'announce_text' => '不明な点はありますか？',
        'unclearBox_trigger' => '不明ありの場合はクリック',
        'unclear_box' => [
            'title' => '該当する不明点にチェック',
        ],
        'item01' => [
            'label' => '開始日',
        ],
        'item02' => [
            'label' => '終了日',
        ],
        'item03' => [
            'label' => '金額',
        ],
        'item04' => [
            'label' => '期間がわからない',
            $boolean_form[0]['key'] => $boolean_form[0]['value'],
            $boolean_form[1]['key'] => $boolean_form[1]['value'],
        ],
        'item05' => [
            'label' => '期間がわからない（コメント）',
            'placeholder' => '内容を入力してください',
        ],
        'item06' => [
            'label' => 'スライドを特定できない',
            $boolean_form[0]['key'] => $boolean_form[0]['value'],
            $boolean_form[1]['key'] => $boolean_form[1]['value'],
        ],
        'item07' => [
            'label' => 'スライドを特定できない（コメント）',
            'placeholder' => '内容を入力してください',
        ],
        'item08' => [
            'label' => 'その他',
            $boolean_form[0]['key'] => $boolean_form[0]['value'],
            $boolean_form[1]['key'] => $boolean_form[1]['value'],
        ],
        'item09' => [
            'label' => 'その他（コメント）',
            'placeholder' => '内容を入力してください',
        ],
        '_modal' => [
            'fix' => [
                'p1' => '作業内容を確定します。',
                'p2' => 'よろしいですか？',
            ],
            'unclear' => [
                'p1' => '「不明あり」で処理します。',
                'p2' => 'よろしいですか？',
            ],
        ],
    ],
        // 最終判定画面用
    'final_judge' => [
        'h1' => '最終判定',
        'target_file' => '対象ファイル',
        'link_guide' => '判定パターンリストを開く',
        'step1' => [
            'step_description' => '判断パターンリストに則って判定結果ボタンをクリックしてください',
            'step_description_sub' => '上図に基づき判定結果ボタンを押す',
            'exist_unclear_tooltip_text' => '作業内容に不明点があります',
        ],
        'item01' => [
            'label' => '判定結果',
        ],
        '_modal' => [
            'fix' => [
                'p1' => '作業内容を確定します。',
                'p2' => 'よろしいですか？',
            ],
            'messages' => [
                'judge_type' => [
                    \App\Http\Controllers\Api\Biz\WfGaisanSyusei\WfGaisanSyuseiController::JUDGE_TYPE_WF_APPLY => '「要WF概算修正」として処理します。',
                    \App\Http\Controllers\Api\Biz\WfGaisanSyusei\WfGaisanSyuseiController::JUDGE_TYPE_WF_APPLY_W_CHANGE => '「要WF概算修正 x 案件概要変更あり」として処理します。',
                    \App\Http\Controllers\Api\Biz\WfGaisanSyusei\WfGaisanSyuseiController::JUDGE_TYPE_FULL_CHARGE => '「満額請求」として処理します。',
                    \App\Http\Controllers\Api\Biz\WfGaisanSyusei\WfGaisanSyuseiController::JUDGE_TYPE_FULL_DIGESTION => '「満額消化」として処理します。',
                    \App\Http\Controllers\Api\Biz\WfGaisanSyusei\WfGaisanSyuseiController::JUDGE_TYPE_SAS_CORRECTED => '「SAS修正済」として処理します。',
                    \App\Http\Controllers\Api\Biz\WfGaisanSyusei\WfGaisanSyuseiController::JUDGE_TYPE_HOLD => 'このタスクを保留にします。',
                    \App\Http\Controllers\Api\Biz\WfGaisanSyusei\WfGaisanSyuseiController::JUDGE_TYPE_RETURN_STEPS => '選択した作業を戻します。',
                ],
                'attention_for_return_to_assort_mail' => '※この作業を戻すと、同じ依頼から発生した下記JOBNOの作業データも同時にリセットされます。',
                'related_jobno' => '関連JOBNO',
            ],
        ],
        '_return_steps_modal' => [
            'header' => 'どの作業を戻しますか？',
            'select_operators_label' => '担当者',
            'comment_label' => '次作業画面へのコメント',
            'alert' => [
                'no_steps_selected' => '作業が選択されていません',
            ],
        ],
        'return_step_valid_msg' => [
            '01' => '※他の作業と同時には戻せません',
            '02' => '※メール仕訳と同時には戻せません',
            '03' => '※パワポ特定と同時には戻せません',
            '04' => '※パワポ情報入力と同時には戻せません',
        ],
    ],
];

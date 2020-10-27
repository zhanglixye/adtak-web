<?php
/*
経費申請の言語テキスト

[呼び出し方法]

'検索'を呼び出す場合
@lang('biz/wf_gaisan_syusei.work.item06')

bladeでの使用例
<button>@lang('biz/wf_gaisan_syusei.work.item06')</button>

blade内のjsで使う場合は、
<?php echo __('biz/wf_gaisan_syusei.work.item06'); ?>
*/

return [
    'common' => [
        'link_to_index' => '一覧に戻る',
    ],
    'improvement' => [
        'title' => '依頼確認',
        'status' => 'ステータス',
        'statuses' => [
            'none' => '未対応',
            'on' => '対応中（保留）',
            'done' => '完了',
            'inactive' => '除外',
        ],
        'development_started_at' => '着手時刻',
        'development_finished_at' => '完了時刻',
        '_modal' => [
            'p1' => '只今の時刻より作業を開始します。よろしいですか？',
            'p2' => '只今の時刻で作業を終了します。よろしいですか？',
            'notify' => 'データの更新に失敗しました。',
        ],
    ],
];

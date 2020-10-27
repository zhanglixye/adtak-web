<?php
/*
メール用テキスト

[呼び出し方法]

'検索'を呼び出す場合
@lang('common.mail.search')

bladeでの使用例
<button>@lang('common.mail.search')</button>

blade内のjsで使う場合は、
<?php echo __('common.mail.search'); ?>
*/

return [
    'file_list' => 'ファイル一覧を開く',
    'attachment_list' => [
        'title' => 'ダウンロード選択:',
        'select_all' => '全件選択',
    ],
    'info' => 'メール情報',
];

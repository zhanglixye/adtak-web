<?php
/*
リクエストコンテント用テキスト

[呼び出し方法]

'検索'を呼び出す場合
@lang('common.request_content.search')

bladeでの使用例
<button>@lang('common.request_content.search')</button>

blade内のjsで使う場合は、
<?php echo __('common.request_content.search'); ?>
*/

return [
    'mail' => '作業依頼',
    'file' => '作業依頼',
    'information' => [
        'request_detail_confirm' => '依頼詳細を確認',
        'work_detail_confirm' => '作業詳細を確認',
    ],
];

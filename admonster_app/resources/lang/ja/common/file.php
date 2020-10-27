<?php
/*
ファイル用テキスト

[呼び出し方法]

'検索'を呼び出す場合
@lang('common.file.search')

bladeでの使用例
<button>@lang('common.file.search')</button>

blade内のjsで使う場合は、
<?php echo __('common.file.search'); ?>
*/

return [
    'file_info' => 'ファイル情報',
    'file_info_details' => [
        'name' => 'ファイル名',
        'request_line_number' => '依頼行番号',
        'capture_datetime' => '取込日時',
        'capture_person' => '取込担当者',
    ],
    'capture_data' => '取込データ',
];

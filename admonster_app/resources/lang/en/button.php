<?php
/*
ボタン用テキスト

[呼び出し方法]

'検索'を呼び出す場合
@lang('common.button.search')

bladeでの使用例
<button>@lang('common.button.search')</button>

blade内のjsで使う場合は、
<?php echo __('common.button.search'); ?>
*/

return [
    'search' => 'search',
    'reset' => 'reset',
    'add' => 'add',
    'save' => 'save',
    'update' => 'update',
    'delete' => 'delete',
    'close' => 'close',
    'cancel' => 'cancel',
    'back' => 'back',
    'download' => 'download'
];

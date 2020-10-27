<?php
/*
作業結果用テキスト

[呼び出し方法]

'検索'を呼び出す場合
@lang('common.task_result.search')

bladeでの使用例
<button>@lang('common.task_result.search')</button>

blade内のjsで使う場合は、
<?php echo __('common.task_result.search'); ?>
*/

return [
    'result_text' => [
        'type' => [
            \Config::get('const.PREFIX').\Config::get('const.TASK_RESULT_TYPE.DONE') => '正常終了',
            \Config::get('const.PREFIX').\Config::get('const.TASK_RESULT_TYPE.CONTACT') => '不明点あり',
            \Config::get('const.PREFIX').\Config::get('const.TASK_RESULT_TYPE.HOLD') => '保留',
            \Config::get('const.PREFIX').\Config::get('const.TASK_RESULT_TYPE.NOT_WORKING') => '未処理',
        ],
    ]
];

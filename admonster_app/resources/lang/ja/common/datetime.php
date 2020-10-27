<?php
/*
日時、時刻表現用テキスト

[呼び出し方法]

@lang('common.message.not_found')
など。

bladeでの使用例
<button>@lang('common.message.not_found')</button>

blade内のjsで使う場合は、
<?php echo __('common.message.not_found'); ?>
*/

return [
    'year' => '年',
    'to_than_also_after' => 'toよりも日付が後になっています',
    'from_than_also_date_is_ago' => 'fromよりも日付が前になっています',
    'month' => '月',
    'day' => '日',
    'date' => '日付',
    'hour' => [
        'point' => '時',
        'interval' => '時間',
    ],
    'minute' => '分',
    'second' => '秒',
    'before' => '前',
    'after' => '後',
    'format' => [
        'year' => 'yyyy',
        'year_month' => 'yyyy/mm',
        'year_month_date' => 'yyyy/mm/dd'
    ]
];

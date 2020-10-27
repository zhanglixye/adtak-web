<?php
/*
[呼び出し方法]

@lang(tasks.index.h1')
など。

■bladeでの使用例
<button>@lang('tasks.index.h1')</button>

blade内のjsで使う場合は、
<?php echo __('tasks.index.h1'); ?>

■vueコンポーネントでの使用例
{{ $t('tasks.index.h1') }}


[注意点]
キーにクラスの定数を使う場合は、Vue.jsでも使えるようにjsonに変換する際に「0」がfalse判定になりVue.js用に生成された言語ファイル内でキーが消滅してしまうので、必ず接頭辞を付与すること。

(例)
\App\Models\Task::STEP_ID_ASSORT_MAIL をキーにする場合
$prefix = 'const'
$prefix.\App\Models\Task::STEP_ID_ASSORT_MAIL
という形にする
*/

$prefix = 'const';

return [
    'index' => [
        'h1' => 'Task List',
    ],
    'step_id_descriptions' => [
        $prefix.\App\Http\Controllers\Api\Biz\WfGaisanSyusei\WfGaisanSyuseiController::STEP_ID_ASSORT_MAIL  => "This is assorted mail's work by assured monetary total and JobNo unit.",
        $prefix.\App\Http\Controllers\Api\Biz\WfGaisanSyusei\WfGaisanSyuseiController::STEP_ID_INPUT_MAIL_INFO => "This is confirm or input appointed project's work from mail.",
        $prefix.\App\Http\Controllers\Api\Biz\WfGaisanSyusei\WfGaisanSyuseiController::STEP_ID_INPUT_PPT_INFO => "This is confirm or input work from mail's content and additional PowerPoint.",
        $prefix.\App\Http\Controllers\Api\Biz\WfGaisanSyusei\WfGaisanSyuseiController::STEP_ID_INPUT_SAS_INFO => "This is input appointed project's work from SAS(other screen)",
    ],
    'statuses' => [
        $prefix.\App\Models\Task::STATUS_NONE => 'unhandled',
        $prefix.\App\Models\Task::STATUS_ON => 'dealing(retention)',
        $prefix.\App\Models\Task::STATUS_DONE => 'finished',
    ],
    'search_condition' => [
        'step_name' => 'work name',
        'created_at_from' => 'date of onset From',
        'created_at_to' => 'date of onset To',
        'deadline_from' => 'deadline of delivery From',
        'deadline_to' => 'deadline of delivery To',
        'business_name' => 'business name',
        'status' => [
            'label' => 'status',
            'none' => 'unhandled',
            'on' => 'handling(retention)',
            'done' => 'finished',
        ],
        'partial_search' => '(searchable partially)',
    ],
    'no_data' => 'no data',
    'business_name' => 'business name',
    'step_name' => 'work name',
    'step_description' => 'work description',
    'created_at' => 'date of onset',
    'deadline' => 'deadline of delivery',
    'status' => 'status',
    'switch_date_type' => 'exchange date',
];

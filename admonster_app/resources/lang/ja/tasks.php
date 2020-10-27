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
\Config::get('const.PREFIX').\App\Models\Task::STEP_ID_ASSORT_MAIL
という形にする
*/

return [
    'index' => [
        'h1' => 'タスク一覧',
    ],
    'statuses' => [
        \Config::get('const.PREFIX').\Config::get('const.TASK_STATUS.NONE') => '未対応',
        \Config::get('const.PREFIX').\Config::get('const.TASK_STATUS.ON') => '対応中（保留）',
        \Config::get('const.PREFIX').\Config::get('const.TASK_STATUS.DONE') => '完了',
    ],
    'search_condition' => [
        'step_name' => '作業名',
        'created_at_from' => '発生日From',
        'created_at_to' => '発生日To',
        'deadline_from' => '納品期限From',
        'deadline_to' => '納品期限To',
        'business_name' => '業務名 / ID',
        'status' => [
            'label' => 'ステータス',
            'none' => '未対応',
            'on' => '対応中（保留）',
            'done' => '完了',
        ],
        'partial_search' => '（部分検索可）',
        'unverified' => '未検証を表示',
    ],
    'no_data' => 'データがありません',
    'show_validation_screen' => '検証画面を表示',
    'business_name' => '業務名',
    'step_name' => '作業名',
    'step_description' => '作業内容',
    'created_at' => '発生日',
    'deadline' => '納品期限',
    'status' => 'ステータス',
    'verification' => '検証',
    'work_type' => '作業種類',
    'education' => '教育',
    'production' => '本番',
    'switch_date_type' => '日付切替',
    'alert' => [
        'message' => [
            'is_inactive' => 'このタスクは無効なステータスです',
            'save_failed_by_inactivated' => 'このタスクは無効なステータスに変更されました'
        ],
    ],
];

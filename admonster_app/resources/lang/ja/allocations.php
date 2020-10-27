<?php

return [
    'list' => [
        'title' => '割振一覧',
        'status' => [
            'none' => '未割振',
            'done' => '割振済'
        ],
        'is_auto' => [
            'false' => '手動',
            'true' => '自動'
        ],
        'switch_to_educational_mode' => '教育割振りモードに切替',
        'confirm_switch_to_educational_mode' => '教育割振りモードに切替えます。よろしいですか。',
        'note_switch_to_educational_mode_1' => '※割振ることができるのは納品済作業のみです。',
        'note_switch_to_educational_mode_2' => '※作業結果は納品されません。',
        'method' => [
            'equality' => '均等',
            'business_volume' => '業務量',
            'learning_level' => '習熟度'
        ],
        'notice' => [
            'unallocated_status' => '割振が行えないステータスの案件が含まれています。',
            'allocating_to_multiple_steps' => '複数作業にわたっての一括割振はできません。',
        ],
        'worker_detail_confirm' => '作業者の詳細を確認'
    ],
    'dialog' => [
        'allocate' => [
            'text' => '選択した担当者に作業を割振ります。',
        ],
    ],
    'single_allocate' => '個別割振',
    'multi_allocate' => '一括割振',
    'collective_assignment' => '一括割振（条件設定）',
    'collective_assignment_confirm' => '一括割振（確認）',
    'back_list' => '一覧に戻る',
    'concurrency' => '並列度',
    'evenness' => '均等度',
    'evenness_type' => [
        'even' => '均等',
        'individual' => '比率を指定',
    ],
    'request_work' => [
        'headers' => [
            'request_work_name' => '依頼作業名',
            'current_work' => '現在の作業',
            'process' => '工程',
            'operator' => '担当者',
            'createdAt' => '発生日',
            'deadline' => '納品期限'
        ],
        'processes' => [
            'allocation' => '割振',
            'work' => '実作業',
            'approval' => '承認',
            'delivery' => '納品',
            'finished' => '済',
        ],

    ],
    'request_content' => [
        'odditional_file' => '件の添付ファイル',

    ],
    'allocation_list'=>[
        'operator' => '担当者',
        'search' => '検索',
        'allocation'=>'割振',
        'headers'=>[
            'user_name'=>'担当者',
            'work_in_process_count'=>'残作業(件)',
            'work_in_process_count_detail'=>'現在割り振られている作業件数',
            'estimated_time'=>'完了目安(分)',
            'estimated_time_detail'=>'現在割り振られている全作業の完了目安時間',
            'completed_count'=>'実績(件)',
            'completed_count_detail'=>'割振対象作業の実績数',
            'average'=>'実績平均(分)',
            'average_detail'=>'割振対象作業の平均作業時間',
            'percentage'=>'正答率(%)',
            'percentage_detail'=>'担当者の正答率'
        ],
        'remind_dialog'=>[
          'icon_name'=>'リマインド設定する'
        ],

    ],


];

<?php

return [
    'list' => [
        'title' => '割振一覧（教育）',
        'status' => [
            'none' => '未割振',
            'done' => '割振済'
        ],
        'switch_to_normal_mode' => '通常割振りモードに切替',
        'list_display_contents' => '※納品済の依頼のみ表示',
        'requests_already_delivered' => '納品済の依頼を',
        'assignment_screen_as_educational' =>'教育タスクとして割振る画面です。',
        'confirm_switch_to_normal_mode' => '通常割振りモードに切替えます。よろしいですか。',
        'is_auto' => [
            'false' => '手動',
            'true' => '自動'
        ],
        'method' => [
            'equality' => '均等',
            'business_volume' => '業務量',
            'learning_level' => '習熟度'
        ],
        'notice' => [
            'unallocated_status' => '対象案件がすべて作業中、あるいは作業済みのため操作できません',
            'allocating_to_multiple_steps' => '複数作業にわたっての一括割振はできません。',
        ],
        'worker_detail_confirm' => '作業者の詳細を確認'
    ],
    'dialog' => [
        'allocate' => [
            'text' => '選択した担当者に作業を割振ります。',
        ],
    ],
    'single_allocate' => '個別割振（教育）',
    'multi_allocate' => '一括割振（教育）',
    'collective_assignment' => '一括割振（条件設定）',
    'collective_assignment_confirm' => '一括割振（確認）',
    'back_list' => '一覧に戻る',
    'concurrency' => '並列度',
    'evenness' => '均等度',
    'total' => '合計',
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
        'number_of_eligible_projects' => '対象案件数',
        'reset' => '条件リセット',
        'next' => '次へ',
        'assign_people' => '人を割振る',
        'not_selected' => '担当者が選択されていません。',
        'per_case' =>'1件につき',
        'default_value' => '規定値:',
        'education_work_status' => [
            'none' => '非表示',
            'done' => '表示'
        ],
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
            'percentage_detail'=>'担当者の正答率',
            'education_work'=>'本番演習',
            'education_work_detail'=>'教育作業であることを担当者に表示しない',
        ],
        'remind_dialog'=>[
            'icon_name'=>'リマインド設定する'
        ],

    ],
    'number_of_occurrences' => '作業件数',
    'target_project_parallel' => '対象案件数 × 並列度',
    'unworked_only' => '（作業中、完了済みを除く）',
    'by_project' => '案件別',
    'by_rep' => '担当者別'


];

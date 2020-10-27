<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class B00008Seeder extends Seeder
{
    /**
     * Run the database seeds.
     * Command: php artisan db:seed --class=B00008Seeder
     *
     * @return void
     */
    public function run()
    {
        // ==============================
        // value settings
        // ==============================

        // business
        $company_id = 1;
        $business = [
            'id' => 8,
            'name' => 'OP統RoutineReport作成業務',
            'description' => '共有Spreadsheet「【セールスサポートチーム】ToDoリスト」より、当日対応案件を抽出し、ClientReportを作成する業務',
            'is_auto_sending_client_page' => \Config::get('const.FLG.INACTIVE'),
            'is_viewable_in_client_page' => \Config::get('const.FLG.INACTIVE'),
        ];

        // step
        $steps = [
            [
                'id' => 14,
                'next_id' => null,
                'name' => 'RoutineReport作成作業',
                'type' => \Config::get('const.STEP_TYPE.INPUT'),
                'description' => 'Client毎に決められたFormatのClientReportを作成する業務',
                'time_unit' => 3,
                'deadline_limit' => 1,
                'end_criteria' => 0,
                'is_send_task_req_mail' => \Config::get('const.FLG.INACTIVE'),
                'allocation_parallel' => 1,
                'allocation_method' => 2,
                'approval_conditions' => 1,
                'is_auto_allocation' => \Config::get('const.FLG.INACTIVE'),
                'is_auto_approval' => \Config::get('const.FLG.INACTIVE'),
                'item_configs' => [
                    "results" => [
                        "label" => "処理結果",
                        "type" => null,
                        "sort" => -1,
                    ],
                    "results/type" => [
                        "label" => null,
                        "type" => 10,
                        "sort" => 0,
                    ],
                    "G00000_27" => [
                        "label" => "作成メール",
                        "type" => null,
                        "sort" => 27,
                    ],
                    "G00000_27/C00300_28" => [
                        "label" => "To",
                        "type" => 300,
                        "sort" => 28,
                    ],
                    "G00000_27/C00300_29" => [
                        "label" => "Cc",
                        "type" => 300,
                        "sort" => 29,
                    ],
                    "G00000_27/C00100_30" => [
                        "label" => "Subject",
                        "type" => 100,
                        "sort" => 30,
                    ],
                    "G00000_27/C00900_31" => [
                        "label" => "本文",
                        "type" => 900,
                        "sort" => 31,
                    ],
                    "G00000_27/C00800_32" => [
                        "label" => "ファイル添付",
                        "type" => 800,
                        "sort" => 32,
                    ],
                    "checklist" => [
                        "label" => "作業内容",
                        "type" => null,
                        "sort" => 100,
                    ],
                    "checklist/G00000_27/G00000_33/0/items/0" => [
                        "label" => "作業件数",
                        "type" => 100,
                        "sort" => 101,
                    ],
                    "checklist/G00000_27/G00000_33/0/items/1" => [
                        "label" => "BoxURL",
                        "type" => 100,
                        "sort" => 102,
                    ],
                    "checklist/G00000_27/G00000_33/1/items/0" => [
                        "label" => null,
                        "type" => 400,
                        "sort" => 103,
                        "values" => [
                            "格納内容に問題はありません",
                        ],
                    ],
                    "checklist/G00000_27/G00000_33/1/items/1" => [
                        "label" => null,
                        "type" => 400,
                        "sort" => 104,
                        "values" => [
                            "添付内容に問題はありません",
                        ],
                    ],
                ],
            ],
        ];

        // common_mail
        $common_mail = [
            'mail_to' => 5,
            'cc' => 5,
            'subject' => 7,
            'body' => 7,
            'mail_template' => 7,
            'sign_template' => 7,
            'file_attachment' => 3,
            'check_list_button' => 3,
            'review' => 3,
            'use_time' => 3,
            'unknown' => 3,
            'save_button' => 3,
            $steps[0]['id'] => [
                'condition_cd' => 1,
                'title' => "",
                'content' => "<p>ご担当者様</p><p><br></p><p>お疲れ様です。#{step_name}の担当#{step_user}です。</p><p>掲題の件、レポート作成が完了しましたので</p><p>以下のURLよりご確認いただけますと幸いです。</p><p>---------------------------------------</p><p><br></p><p>---------------------------------------</p><p><br></p><p>▼連絡事項</p><p>---------------------------------------</p><p><br></p><p>---------------------------------------</p><p><br></p><p>追加・修正などございましたらご連絡いただけますと幸いです。</p><p><br></p><p>以上</p><p>何卒よろしくお願いいたします。</p><p><br></p><p> #{signature} </p>",
                'checklist' => [
                    [
                        'content' => '作業チェック内容１',
                        'items' => [
                            ['component_type' => 1, 'content' => '作業件数'],
                            // ['component_type' => 1, 'content' => 'BoxURL'],  // ADPORTER_PF-228で削除
                        ],
                    ],
                    [
                        'content' => '作業チェック内容２',
                        'items' => [
                            ['component_type' => 0, 'content' => '格納内容に問題はありません'],
                            ['component_type' => 0, 'content' => '添付内容に問題はありません'],
                        ]
                    ]
                ]
            ],
        ];

        // file_import
        $file_import = [
            $steps[0]['id'] => [
                'sheet_name' => null,
                'header_row_no' => 2,
                'data_start_row_no' => 3,
                'subject_delimiter' => '_',
                'columns' => [
                    [
                        'column' => 'A',
                        'label' => '日付',
                        'item_key' => 'item_1',
                        'item_type' => 3,
                        'data_type' => 3,
                        'subject_part_no' => null,
                        'request_info_type' => null,
                    ],
                    [
                        'column' => 'C',
                        'label' => '業務名',
                        'item_key' => 'mail_subject1',
                        'item_type' => 1,
                        'data_type' => 3,
                        'subject_part_no' => 1,
                        'request_info_type' => null,
                    ],
                    [
                        'column' => 'D',
                        'label' => '管理NO.',
                        'item_key' => 'item_3',
                        'item_type' => 1,
                        'data_type' => 3,
                        'subject_part_no' => null,
                        'request_info_type' => null,
                    ],
                    [
                        'column' => 'F',
                        'label' => 'コメント',
                        'item_key' => 'item_4',
                        'item_type' => 1,
                        'data_type' => 3,
                        'subject_part_no' => null,
                        'request_info_type' => null,
                    ],
                    [
                        'column' => 'N',
                        'label' => 'To',
                        'item_key' => 'mail_to',
                        'item_type' => 1,
                        'data_type' => 3,
                        'subject_part_no' => null,
                        'request_info_type' => null,
                    ],
                    [
                        'column' => 'O',
                        'label' => 'Cc',
                        'item_key' => 'mail_cc',
                        'item_type' => 1,
                        'data_type' => 3,
                        'subject_part_no' => null,
                        'request_info_type' => null,
                    ],
                    [
                        'column' => 'I',
                        'label' => '業務管理区分',
                        'item_key' => 'item_7',
                        'item_type' => 1,
                        'data_type' => 3,
                        'subject_part_no' => 2,
                        'request_info_type' => null,
                    ],
                    [
                        'column' => 'J',
                        'label' => '依頼主',
                        'item_key' => 'item_8',
                        'item_type' => 1,
                        'data_type' => 3,
                        'subject_part_no' => 3,
                        'request_info_type' => 1,
                    ],
                    [
                        'column' => 'K',
                        'label' => '収益管理区分',
                        'item_key' => 'item_9',
                        'item_type' => 1,
                        'data_type' => 3,
                        'subject_part_no' => null,
                        'request_info_type' => null,
                    ],
                    [
                        'column' => 'L',
                        'label' => '納期',
                        'item_key' => 'item_10',
                        'item_type' => 1,
                        'data_type' => 3,
                        'subject_part_no' => null,
                        'request_info_type' => 2,
                    ],
                    [
                        'column' => 'M',
                        'label' => '曜日',
                        'item_key' => 'item_11',
                        'item_type' => 1,
                        'data_type' => 3,
                        'subject_part_no' => null,
                        'request_info_type' => null,
                    ],
                ],
            ],
        ];

        // const
        $language_id = 2;
        $datetime_now = Carbon::now();
        $system_user_id = 0;

        $created_columns = [
            'created_at' => $datetime_now,
            'created_user_id' => $system_user_id
        ];
        $updated_columns = [
            'updated_at' => $datetime_now,
            'updated_user_id' => $system_user_id
        ];

        // ==============================
        // save master data
        // ==============================

        \DB::beginTransaction();
        try {
            // ==============================
            // business and step settings
            // ==============================
            if (\DB::table('businesses')->where('id', $business['id'])->doesntExist()) {
                \Db::table('businesses')->insert(
                    [
                        'id' => $business['id'],
                        'name' => $business['name'],
                        'company_id' => $company_id,
                        'description' => $business['description'],
                        'is_deleted' => \Config::get('const.FLG.INACTIVE'),
                    ] + $created_columns + $updated_columns
                );
            } else {
                \Db::table('businesses')->where('id', $business['id'])->update(
                    [
                        'name' => $business['name'],
                        'company_id' => $company_id,
                        'description' => $business['description'],
                    ] + $updated_columns
                );
            }

            foreach ($steps as $step) {
                if (\DB::table('steps')->where('id', $step['id'])->doesntExist()) {
                    \DB::table('steps')->insert(
                        [
                            'id' => $step['id'],
                            'name' => $step['name'],
                            'step_type' => $step['type'],
                            'url' => sprintf('b%05d/s%05d', $business['id'], $step['id']),
                            'description' => $step['description'],
                            'time_unit' => $step['time_unit'],
                            'deadline_limit' => $step['deadline_limit'],
                            'end_criteria' => $step['end_criteria'],
                            'is_send_task_req_mail' => $step['is_send_task_req_mail'],
                            'is_deleted' => \Config::get('const.FLG.INACTIVE'),
                        ] + $created_columns + $updated_columns
                    );
                } else {
                    \DB::table('steps')->where('id', $step['id'])->update(
                        [
                            'name' => $step['name'],
                            'step_type' => $step['type'],
                            'description' => $step['description'],
                            'time_unit' => $step['time_unit'],
                            'deadline_limit' => $step['deadline_limit'],
                            'end_criteria' => $step['end_criteria'],
                            'is_send_task_req_mail' => $step['is_send_task_req_mail'],
                        ] + $updated_columns
                    );
                }

                if (\DB::table('business_flows')->where('step_id', $step['id'])->doesntExist()) {
                    \DB::table('business_flows')->insert(
                        [
                            'step_id' => $step['id'],
                            'seq_no' => 1,
                            'business_id' => $business['id'],
                            'next_step_id' => $step['next_id'],
                        ] + $created_columns + $updated_columns
                    );
                } else {
                    \DB::table('business_flows')->where('step_id', $step['id'])->update(
                        [
                            'business_id' => $business['id'],
                            'next_step_id' => $step['next_id'],
                        ] + $updated_columns
                    );
                }

                if (DB::table('allocation_configs')->where('step_id', $step['id'])->doesntExist()) {
                    \DB::table('allocation_configs')->insert(
                        [
                            'step_id' => $step['id'],
                            'parallel' => $step['allocation_parallel'],
                            'is_auto' => $step['is_auto_allocation'],
                            'method' => $step['allocation_method'],
                        ] + $created_columns + $updated_columns
                    );
                } else {
                    \DB::table('allocation_configs')->where('step_id', $step['id'])->update(
                        [
                            'parallel' => $step['allocation_parallel'],
                            'is_auto' => $step['is_auto_allocation'],
                            'method' => $step['allocation_method'],
                        ] + $updated_columns
                    );
                }

                if (DB::table('approval_configs')->where('step_id', $step['id'])->doesntExist()) {
                    \DB::table('approval_configs')->insert(
                        [
                            'step_id' => $step['id'],
                            'is_auto' => $step['is_auto_approval'],
                            'conditions' => $step['approval_conditions'],
                        ] + $created_columns + $updated_columns
                    );
                } else {
                    \DB::table('approval_configs')->where('step_id', $step['id'])->update(
                        [
                            'is_auto' => $step['is_auto_approval'],
                            'conditions' => $step['approval_conditions'],
                        ] + $updated_columns
                    );
                }
            }

            // ==============================
            // client config settings
            // ==============================
            if (\DB::table('guest_client_issue_configs')->where('business_id', $business['id'])->doesntExist()) {
                \DB::table('guest_client_issue_configs')->insert(
                    [
                        'business_id' => $business['id'],
                        'is_auto' => $business['is_auto_sending_client_page'],
                    ] + $created_columns + $updated_columns
                );
            } else {
                \DB::table('guest_client_issue_configs')->where('business_id', $business['id'])->update(
                    [
                        'is_auto' => $business['is_auto_sending_client_page'],
                    ] + $updated_columns
                );
            }

            if (\DB::table('client_default_configs')->where('business_id', $business['id'])->doesntExist()) {
                \DB::table('client_default_configs')->insert(
                    [
                        'business_id' => $business['id'],
                        'is_viewable_request_related_mails' => $business['is_viewable_in_client_page'],
                        'is_viewable_request_additional_infos' => $business['is_viewable_in_client_page'],
                    ] + $created_columns + $updated_columns
                );
            } else {
                \DB::table('client_default_configs')->where('business_id', $business['id'])->update(
                    [
                        'is_viewable_request_related_mails' => $business['is_viewable_in_client_page'],
                        'is_viewable_request_additional_infos' => $business['is_viewable_in_client_page'],
                    ] + $updated_columns
                );
            }

            // ==============================
            // item config settings
            // ==============================
            foreach ($steps as $step) {
                \DB::table('item_configs')->where('step_id', $step['id'])->update(
                    ['is_deleted' => \Config::get('const.FLG.ACTIVE')] + $updated_columns
                );

                foreach ($step['item_configs'] as $key => $obj) {
                    $label_id = null;
                    if (isset($obj['label'])) {
                        $label_id = \DB::table('labels')->insertGetId(
                            [
                                'language_id' => $language_id,
                                'name' => $obj['label'],
                            ] + $created_columns + $updated_columns
                        );
                    }
                    $item_config_id = \DB::table('item_configs')->insertGetId(
                        [
                            'step_id' => $step['id'],
                            'sort' => $obj['sort'],
                            'label_id' => $label_id,
                            'item_key' => $key,
                            'item_type' => $obj['type'],
                        ] + $created_columns + $updated_columns
                    );
                    if (isset($obj['values'])) {
                        foreach ($obj['values'] as $sort => $label) {
                            $label_id = \DB::table('labels')->insertGetId(
                                [
                                    'language_id' => $language_id,
                                    'name' => $label,
                                ] + $created_columns + $updated_columns
                            );
                            \DB::table('item_config_values')->insert(
                                [
                                    'item_config_id' => $item_config_id,
                                    'sort' => $sort,
                                    'label_id' => $label_id,
                                ] + $created_columns + $updated_columns
                            );
                        }
                    }
                }
            }

            // ==============================
            // common mail settings
            // ==============================
            \DB::table('common_mail_settings')->where('business_id', $business['id'])->update(
                ['is_deleted' => \Config::get('const.FLG.ACTIVE')] + $updated_columns
            );
            \DB::table('common_mail_settings')->insert(
                [
                    'business_id' => $business['id'],
                    'mail_to' => $common_mail['mail_to'],
                    'cc' => $common_mail['cc'],
                    'subject' => $common_mail['subject'],
                    'body' => $common_mail['body'],
                    'mail_template' => $common_mail['mail_template'],
                    'sign_template' => $common_mail['sign_template'],
                    'file_attachment' => $common_mail['file_attachment'],
                    'check_list_button' => $common_mail['check_list_button'],
                    'review' => $common_mail['review'],
                    'use_time' => $common_mail['use_time'],
                    'unknown' => $common_mail['unknown'],
                    'save_button' => $common_mail['save_button'],
                    'is_deleted' => \Config::get('const.FLG.INACTIVE'),
                ] + $created_columns + $updated_columns
            );

            foreach ($steps as $key => $step) {
                \DB::table('common_mail_body_templates')->where('step_id', $step['id'])->update(
                    ['is_deleted' => \Config::get('const.FLG.ACTIVE')] + $updated_columns
                );
                \DB::table('common_mail_body_templates')->insert(
                    [
                        'company_id' => $company_id,
                        'business_id' => $business['id'],
                        'step_id' => $step['id'],
                        'condition_cd' => $common_mail[$step['id']]['condition_cd'],
                        'title' => $common_mail[$step['id']]['title'],
                        'content' => $common_mail[$step['id']]['content'],
                        'is_deleted' => \Config::get('const.FLG.INACTIVE'),
                    ] + $created_columns + $updated_columns
                );

                $checklist_group_ids = \DB::table('common_mail_checklist_groups')
                    ->where('step_id', $step['id'])
                    ->pluck('id');

                \DB::table('common_mail_checklist_groups')->whereIn('id', $checklist_group_ids)->update(
                    ['is_deleted' => \Config::get('const.FLG.ACTIVE')] + $updated_columns
                );
                \DB::table('common_mail_checklist_items')->whereIn('group_id', $checklist_group_ids)->update(
                    ['is_deleted' => \Config::get('const.FLG.ACTIVE')] + $updated_columns
                );
                foreach ($common_mail[$step['id']]['checklist'] as $group_key => $group) {
                    $group_id = \DB::table('common_mail_checklist_groups')->insertGetId(
                        [
                            'company_id' => $company_id,
                            'business_id' => $business['id'],
                            'step_id' => $step['id'],
                            'content' => $group['content'],
                            'order_num' => $group_key,
                            'is_deleted' => \Config::get('const.FLG.INACTIVE'),
                        ] + $created_columns + $updated_columns
                    );
                    foreach ($group['items'] as $key => $item) {
                        \DB::table('common_mail_checklist_items')->insert([
                            [
                                'group_id' => $group_id,
                                'content' => $item['content'],
                                'component_type' => $item['component_type'],
                                'order_num' => $key,
                                'is_deleted' => \Config::get('const.FLG.INACTIVE'),
                            ] + $created_columns + $updated_columns
                        ]);
                    }
                }
            }

            // ==============================
            // file import settings
            // ==============================
            foreach ($steps as $key => $step) {
                $main_config_id = \DB::table('file_import_main_configs')
                    ->where('step_id', $step['id'])->value('id');
                if (\DB::table('file_import_main_configs')->where('step_id', $step['id'])->doesntExist()) {
                    $main_config_id = \Db::table('file_import_main_configs')->insertGetId(
                        [
                            'step_id' => $step['id'],
                            'sheet_name' => $file_import[$step['id']]['sheet_name'],
                            'header_row_no' => $file_import[$step['id']]['header_row_no'],
                            'data_start_row_no' => $file_import[$step['id']]['data_start_row_no'],
                            'subject_delimiter' => $file_import[$step['id']]['subject_delimiter'],
                        ] + $created_columns + $updated_columns
                    );
                } else {
                    \Db::table('file_import_main_configs')->where('step_id', $step['id'])->update(
                        [
                            'sheet_name' => $file_import[$step['id']]['sheet_name'],
                            'header_row_no' => $file_import[$step['id']]['header_row_no'],
                            'data_start_row_no' => $file_import[$step['id']]['data_start_row_no'],
                            'subject_delimiter' => $file_import[$step['id']]['subject_delimiter'],
                        ] + $updated_columns
                    );
                }

                // file_import_column_configs.is_deleted が機能していないので古いデータは削除
                \DB::table('file_import_column_configs')
                    ->where('file_import_main_config_id', $main_config_id)->delete();
                // \DB::table('file_import_column_configs')->where('file_import_main_config_id', $main_config_id)
                //     ->update(['is_deleted' => \Config::get('const.FLG.ACTIVE')] + $updated_columns);

                foreach ($file_import[$step['id']]['columns'] as $key => $obj) {
                    $label_id = \DB::table('labels')->insertGetId(
                        [
                            'language_id' => $language_id,
                            'name' => $obj['label'],
                        ] + $created_columns + $updated_columns
                    );

                    \DB::table('file_import_column_configs')->insert(
                        [
                            'file_import_main_config_id' => $main_config_id,
                            'label_id' => $label_id,
                            'column' => $obj['column'],
                            'item_key' => $obj['item_key'],
                            'item_type' => $obj['item_type'],
                            'data_type' => $obj['data_type'],
                            'sort' => $key,
                            'subject_part_no' => $obj['subject_part_no'],
                            'request_info_type' => $obj['request_info_type'],
                        ] + $created_columns + $updated_columns
                    );
                }
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
        }
    }
}

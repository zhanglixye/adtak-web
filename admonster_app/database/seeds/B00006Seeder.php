<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class B00006Seeder extends Seeder
{
    /**
     * Run the database seeds.
     * Command: php artisan db:seed --class=B00006Seeder
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
            'id' => 6,
            'name' => '経費申請',
            'description' => '経費の申請内容を確認、承認する業務',
            'is_auto_sending_client_page' => \Config::get('const.FLG.INACTIVE'),
            'is_viewable_in_client_page' => \Config::get('const.FLG.INACTIVE'),
            'reports' => [
                [
                    'name' => '月次レポート',
                    'description' => '<h4>[経費申請]月次レポート</h4><br>会計年月、振込日を選択してください。<br>対象の会計年月でレポートを出力します。<br>社員番号でソートした状態で出力されます。',
                    'identifier' => 'keihiMonthlyReport'
                ]
            ],
        ];

        // step
        $steps = [
            [
                'id' => 11,
                'next_id' => 12,
                'name' => '経費伝票check',
                'type' => \Config::get('const.STEP_TYPE.INPUT'),
                'description' => '経費申請内容を確認する',
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
                    "G00000_1" => [
                        "label" => "チェック内容",
                        "type" => null,
                        "sort" => 1,
                    ],
                    "G00000_1/C00500_2" => [
                        "label" => "『★交通費（AP）メール』のシートに記載がありますか？",
                        "type" => 500,
                        "sort" => 2,
                        "values" => ["はい", "いいえ"],
                    ],
                    "G00000_1/C00100_3" => [
                        "label" => "社員番号",
                        "type" => 100,
                        "sort" => 3,
                    ],
                    "G00000_1/C00100_4" => [
                        "label" => "フリガナ",
                        "type" => 100,
                        "sort" => 4,
                    ],
                    "G00000_1/C00100_5" => [
                        "label" => "氏名",
                        "type" => 100,
                        "sort" => 5,
                    ],
                    "G00000_1/C00100_6" => [
                        "label" => "申請合計金額",
                        "type" => 101,
                        "sort" => 6,
                    ],
                    "G00000_1/C00100_7" => [
                        "label" => "会計年月",
                        "type" => 701,
                        "sort" => 7,
                    ],
                    "C00800_24" => [
                        "label" => "交通費PDF",
                        "type" => null,
                        "sort" => 24,
                    ],
                    "C00800_24/C00800_25" => [
                        "label" => "『★交通費(AP)メール』シートをPDFにしてUPして下さい。",
                        "type" => null,
                        "sort" => 25,
                    ],
                    "C00800_24/C00800_26" => [
                        "label" => "『★交通費(常駐)メール』シートをPDFにしてUPして下さい。",
                        "type" => null,
                        "sort" => 26,
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
                    // "G00000_27/C00300_29" => [  // 非表示
                    //     "label" => "Cc",
                    //     "type" => 300,
                    //     "sort" => 29,
                    // ],
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
                        "label" => null,
                        "type" => 400,
                        "sort" => 101,
                        "values" => [
                            "メール本文に記載されている社員番号、申請者名、金額に問題はありません",
                        ],
                    ],
                    "checklist/G00000_27/G00000_33/0/items/1" => [
                        "label" => null,
                        "type" => 400,
                        "sort" => 102,
                        "values" => [
                            "添付ファイルに問題はありません",
                        ],
                    ],
                ],
            ],
            [
                'id' => 12,
                'next_id' => null,
                'name' => '経費伝票承認',
                'type' => \Config::get('const.STEP_TYPE.APPROVAL'),
                'description' => '経費申請内容を承認する',
                'time_unit' => 3,
                'deadline_limit' => 1,
                'end_criteria' => 0,
                'is_send_task_req_mail' => \Config::get('const.FLG.INACTIVE'),
                'allocation_parallel' => 1,
                'allocation_method' => 2,
                'approval_conditions' => 1,
                'is_auto_allocation' => \Config::get('const.FLG.INACTIVE'),  // 本番はACTIVE
                'is_auto_approval' => \Config::get('const.FLG.INACTIVE'),  // 本番はACTIVE
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
                        "label" => null,
                        "type" => 400,
                        "sort" => 101,
                        "values" => [
                            "メール本文に記載されている社員番号、申請者名、金額に問題はありません",
                        ],
                    ],
                    "checklist/G00000_27/G00000_33/0/items/1" => [
                        "label" => null,
                        "type" => 400,
                        "sort" => 102,
                        "values" => [
                            "添付ファイルに問題はありません",
                        ],
                    ],
                ],
            ],
        ];

        // common_mail
        $common_mail = [
            'mail_to' => 31,
            'cc' => 31,
            'subject' => 7,
            'body' => 7,
            'mail_template' => 7,
            'sign_template' => 7,
            'file_attachment' => 5,
            'check_list_button' => 3,
            'review' => 3,
            'use_time' => 3,
            'unknown' => 0,
            'save_button' => 3,
            $steps[0]['id'] => [
                'condition_cd' => 1,
                'title' => "不備・不明なしの場合",
                'content' => "<p>ご担当者様</p><p><br></p><p>お疲れ様です。#{step_name}の担当#{step_user}です。</p><p>経費伝票のCheck業務が終了しましたので、承認をお願いします。</p><p><br></p><p>会計年月： #{date}</p><p>社員番号： #{employee_id}</p><p>申請者名： #{employee_name}#{employee_spell}</p><p>合計金額： #{amount}</p><p><br></p><p>#{signature}</p><p>------------------ Original ------------------</p><p>&gt;From: #{before_mail_from}</p><p>&gt;Date:#{before_mail_date}</p><p>&gt;Subject:#{before_mail_subject}</p><p>&gt;To:#{before_mail_to}</p><p>&gt;CC:#{before_mail_cc}</p><p>#{before_mail_body}</p>",
                'checklist' => [
                    [
                        'content' => '',
                        'items' => [
                            ['component_type' => 0, 'content' => 'メール本文に記載されている社員番号、申請者名、金額に問題はありません'],
                            ['component_type' => 0, 'content' => '添付ファイルに問題はありません'],
                        ],
                    ],
                ]
            ],
            $steps[1]['id'] => [
                'condition_cd' => 1,
                'title' => "『★交通費(常駐)メール』シートが含まれる場合",
                'content' => "ご担当者様<br/>
                <br/>
                お疲れ様です。#{step_name}の担当#{step_user}です。<br/>
                経費伝票のCheck業務が終了しましたので、ご確認をお願いいたします。<br/>
                <br/>
                会計年月： #{date}<br/>
                社員番号： #{employee_id}<br/>
                申請者名： #{employee_name}#{employee_spell}<br/>
                合計金額： #{amount}<br/>
                <br/>
                #{signature}
                <br/>
                ------------------ Original ------------------
                <br/>
                >From: #{before_mail_from}
                <br/>
                >Date:#{before_mail_date}
                <br/>
                >Subject:#{before_mail_subject}
                <br/>
                >To:#{before_mail_to}
                <br/>
                >CC:#{before_mail_cc}
                <br/>
                #{before_mail_body}",
                'checklist' => [
                    [
                        'content' => '',
                        'items' => [
                            ['component_type' => 0, 'content' => 'メール本文に記載されている社員番号、申請者名、金額に問題はありません'],
                            ['component_type' => 0, 'content' => '添付ファイルに問題はありません'],
                        ]
                    ],
                ]
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
            // custom report settings
            // ==============================
            foreach ($business['reports'] as $report) {
                $businesses_reports = \DB::table('reports')
                    ->join('businesses_reports', 'businesses_reports.report_id', '=', 'reports.id')
                    ->where('businesses_reports.business_id', $business['id'])
                    ->where('reports.identifier', $report['identifier']);

                if ($businesses_reports->exists()) {
                    $report_id = $businesses_reports->value('reports.id');
                    \DB::table('reports')->where('reports.id', $report_id)->update(
                        [
                            'name' => $report['name'],
                            'description' => $report['description'],
                            'is_deleted' => \Config::get('const.FLG.INACTIVE')
                        ] + $updated_columns
                    );
                } else {
                    $report_id = \DB::table('reports')->insertGetId(
                        [
                            'name' => $report['name'],
                            'description' => $report['description'],
                            'identifier' => $report['identifier']
                        ] + $created_columns + $updated_columns
                    );

                    \DB::table('businesses_reports')->insert(
                        [
                            'business_id' => $business['id'],
                            'report_id' => $report_id
                        ] + $created_columns + $updated_columns
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

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
        }
    }
}

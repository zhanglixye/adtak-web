<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class B00014Seeder extends Seeder
{
    /**
     * Run the database seeds.
     * Command: php artisan db:seed --class=B00014Seeder
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
            'id' => 14,
            'name' => 'AG登録・媒体資料紐づけ（長春）',
            'description' => 'AGにメニュー情報と媒体資料を登録する',
            'is_auto_sending_client_page' => \Config::get('const.FLG.INACTIVE'),
            'is_viewable_in_client_page' => \Config::get('const.FLG.INACTIVE'),
            'reports' => [
                [
                    'name' => '期間指定レポート',
                    'description' => '指定期間の件数・手動工数レポートです。<br><br>[対象業務]<br>・AG登録・媒体資料紐づけ<br>・Xone媒体資料紐づけ<br><br>[レポート項目]<br>・日付：作業の完了日<br>・業務<br>・作業者<br>・件数：各業務画面で入力した件数<br>（1）AG登録・媒体資料紐づけ　…リリースメニュー数<br>（2）Xone媒体資料紐づけ　…登録（紐づけ）メニュー数<br>・手動工数：作業画面で手動入力した工数<br>・演習：演習モードの実績には○がつきます<br><br>※システム工数は標準レポートでご確認ください。<br>※納品結果に関わらず、全ての作業者の実績が出力されます。',
                    'identifier' => 'agRegisterReport'
                ],
            ],
        ];

        // step
        $steps = [
            [
                'id' => 20,
                'next_id' => null,
                'name' => 'AG登録・媒体資料紐づけ（長春）',
                'type' => \Config::get('const.STEP_TYPE.INPUT'),
                'description' => 'AGにメニュー情報と媒体資料を登録する',
                'time_unit' => 3,
                'deadline_limit' => 1,
                'end_criteria' => 0,
                'is_send_task_req_mail' => \Config::get('const.FLG.INACTIVE'),
                'allocation_parallel' => 1,
                'allocation_method' => 2,
                'approval_conditions' => 1,
                'is_auto_allocation' => \Config::get('const.FLG.ACTIVE'),
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
                        "sort" => 99,
                    ],
                    "checklist/G00000_27/G00000_33/0/items/0" => [
                        "label" => "リリースメニュー数",
                        "type" => 100,
                        "sort" => 100,
                    ],
                    "checklist/G00000_27/G00000_33/1/items/0" => [
                        "label" => "媒体資料をAGに登録（紐づけ）しましたか？",
                        "type" => 400,
                        "sort" => 110,
                        "values" => [
                            "完了",
                        ],
                    ],
                    "checklist/G00000_27/G00000_33/1/items/1" => [
                        "label" => "媒体資料をAGに登録（紐づけ）しましたか？",
                        "type" => 400,
                        "sort" => 111,
                        "values" => [
                            "不要",
                        ],
                    ],
                    "checklist/G00000_27/G00000_33/2/items/0" => [
                        "label" => "紐づけたデバイス",
                        "type" => 400,
                        "sort" => 120,
                        "values" => [
                            "Premium",
                        ],
                    ],
                    "checklist/G00000_27/G00000_33/2/items/1" => [
                        "label" => "紐づけたデバイス",
                        "type" => 400,
                        "sort" => 121,
                        "values" => [
                            "SD",
                        ],
                    ],
                    "checklist/G00000_27/G00000_33/2/items/2" => [
                        "label" => "紐づけたデバイス",
                        "type" => 400,
                        "sort" => 122,
                        "values" => [
                            "Mobile",
                        ],
                    ],
                    "checklist/G00000_27/G00000_33/2/items/3" => [
                        "label" => "紐づけたデバイス",
                        "type" => 400,
                        "sort" => 123,
                        "values" => [
                            "Mail",
                        ],
                    ],
                    "checklist/G00000_27/G00000_33/2/items/4" => [
                        "label" => "紐づけたデバイス",
                        "type" => 400,
                        "sort" => 124,
                        "values" => [
                            "Targetting",
                        ],
                    ],
                    "checklist/G00000_27/G00000_33/2/items/5" => [
                        "label" => "紐づけたデバイス",
                        "type" => 400,
                        "sort" => 125,
                        "values" => [
                            "その他",
                        ],
                    ],
                    "checklist/G00000_27/G00000_33/3/items/0" => [
                        "label" => "前Q資料の確認・対応をしましたか？",
                        "type" => 400,
                        "sort" => 130,
                        "values" => [
                            "はい",
                        ],
                    ],
                    "checklist/G00000_27/G00000_33/4/items/0" => [
                        "label" => "メールにチェックリストのboxURLの記述がありますか？",
                        "type" => 400,
                        "sort" => 140,
                        "values" => [
                            "はい",
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
            'file_attachment' => 7,
            'check_list_button' => 3,
            'review' => 3,
            'use_time' => 3,
            'unknown' => 3,
            'save_button' => 3,
            $steps[0]['id'] => [
                'condition_cd' => 1,
                'title' => "不備・不明なしの場合",
                'content' => "<div>ご担当者様</div><div><br></div><div>いつもお世話になっております。</div><div>AG登録担当の#{step_user}です。</div><div><br></div><div>下記サイトにつきましてAGの登録が完了いたしました。</div><div><br></div><div>サイト名：#{site_name}</div><div>Q：#{Q}</div><div>媒体資料URL：#{media_info_url}</div><div>媒体資料ファイル名：#{media_info_file_name}</div><div>チェックリスト：#{check_list_url}</div><div><br></div><div>備考</div><div>ｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰ</div><div><br></div><div>ｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰｰ</div><div><br></div><div>ご対応のほどよろしくお願いいたします。</div><div><br></div><div>#{signature}</div>",
                'checklist' => [
                    [
                        'content' => '１．リリースメニュー数を記入してください',
                        'items' => [
                            ['component_type' => 1, 'content' => null],
                        ],
                    ],
                    [
                        'content' => '２．媒体資料をAGに登録（紐づけ）しましたか？　 ※社外秘資料は紐づけ不可！',
                        'items' => [
                            ['component_type' => 2, 'content' => '完了'],
                            ['component_type' => 2, 'content' => '不要'],
                        ]
                    ],
                    [
                        'content' => '2‐1. 紐づけたデバイスにチェックを入れてください',
                        'items' => [
                            ['component_type' => 0, 'content' => 'Premium'],
                            ['component_type' => 0, 'content' => 'SD'],
                            ['component_type' => 0, 'content' => 'Mobile'],
                            ['component_type' => 0, 'content' => 'Mail'],
                            ['component_type' => 0, 'content' => 'Targetting'],
                            ['component_type' => 0, 'content' => 'その他'],
                        ]
                    ],
                    [
                        'content' => '2‐2.前Q資料の確認・対応をしましたか？',
                        'items' => [
                            ['component_type' => 0, 'content' => 'はい'],
                        ]
                    ],
                    [
                        'content' => '３．メールにチェックリストのboxURLの記述がありますか？',
                        'items' => [
                            ['component_type' => 0, 'content' => 'はい'],
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

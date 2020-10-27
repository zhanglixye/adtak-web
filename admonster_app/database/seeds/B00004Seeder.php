<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class B00004Seeder extends Seeder
{
    /**
     * Run the database seeds.
     * Command: php artisan db:seed --class=B00004Seeder
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
            'id' => 4,
            'name' => 'Xoneチームステータス管理業務',
            'description' => '納品メールはないため、作業ステータスのみを管理する業務',
            'is_auto_sending_client_page' => \Config::get('const.FLG.INACTIVE'),
            'is_viewable_in_client_page' => \Config::get('const.FLG.INACTIVE'),
        ];

        // step
        $steps = [
            [
                'id' => 9,
                'next_id' => null,
                'name' => 'Xoneチームステータス管理業務',
                'type' => \Config::get('const.STEP_TYPE.INPUT'),
                'description' => '納品メールはないため、作業ステータスのみを管理する業務',
                'time_unit' => 3,
                'deadline_limit' => 1,
                'end_criteria' => 0,
                'is_send_task_req_mail' => \Config::get('const.FLG.INACTIVE'),
                'allocation_parallel' => 1,
                'allocation_method' => 2,
                'approval_conditions' => 1,
                'is_auto_allocation' => \Config::get('const.FLG.INACTIVE'),
                'is_auto_approval' => \Config::get('const.FLG.ACTIVE'),
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

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
        }
    }
}

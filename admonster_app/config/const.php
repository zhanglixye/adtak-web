<?php

return [
    'APP_NAME' => env('APP_NAME', 'ADPORTER'),
    'PREFIX' => 'const',
    'UNIX_TIME_SERIAL_VALUE' => 25569, // UNIX Time の基準時刻 (1970/01/01(木) 00:00:00 UTC) に相当するシリアル値
    'SYSTEM_USER_ID' => 1,
    'REQUEST_MAIL_BODY_MAX_LENGTH_ON_LIST' => 200,
    'FLG' => [
        'INACTIVE' => 0,
        'ACTIVE' => 1,
    ],
    'API_STATUS_CODE' => [
        // SUCCESS 200~
        'SUCCESS' => 200,
        // ERROR 400~
        'EXCLUSIVE' => 409,
        'OTHERS' => 500
    ],
    'ACTIVE_FLG' => [
        'INACTIVE' => 0,
        'ACTIVE' => 1,
    ],
    'DATE_TYPE' => [
        'CREATED' => 1,
        'DEADLINE' => 2,
        'DELIVERED' => 3,
    ],
    'DELETE_FLG' => [
        'ACTIVE' => 0,
        'INACTIVE' => 1,
    ],
    'PROCESS_TYPE' => [
        'ALLOCATION' => 1,
        'WORK' => 2,
        'APPROVAL' => 3,
        'DELIVERY' => 4,
    ],
    'COMPLETED_PROCESS_TYPE' => [
        'IMPORT' => 1,
        'ALLOCATION' => 2,
        'WORK' => 3,
        'APPROVAL' => 4,
        'DELIVERY' => 5,
    ],
    'REQUEST_WORK_ACTIVE_FLG' => [
        'INACTIVE' => 0,
        'ACTIVE' => 1,
    ],
    'TASK_STATUS' => [
        'NONE' => 0,
        'ON' => 1,
        'DONE' => 2,
    ],
    'TASK_RESULT_TYPE' => [
        'NOT_WORKING' => -1,
        'DONE' => 0,
        'CONTACT' => 1,
        'RETURN' => 2,
        'CANCEL' => 3,
        'HOLD' => 4,
    ],
    'CONTENT_TYPE' => [
        'TEXT' => 1,
        'HTML' => 2,
    ],
    'ITEM_CONFIG_TYPE' => [
        'STRING' => 1,
        'ARRAY' => 2,
        'MAP' => 3,
        'ARRAY_MAP' => 4,
        'FILE' => 5,
        'IMAGE' => 6,
        'EMAIL' => 7,
        'URL' => 8,
        'TEXT_AREA' => 9,
        'TASK_RESULT' => 10,
        'TEXT_BOX' => 100,
        'TEXT_BOX_1' => 101,
        'TEXTAREA' => 200,
        'COMBOBOX' => 300,
        'CHECKBOX' => 400,
        'RADIO' => 500,
        'SELECT' => 600,
        'PICKERS' => 700,
        'FILE_INPUT' => 800,
        'SINGLE_FILE_INPUT' => 801,
        'TEXT_EDITOR' => 900,
    ],
    'APPROVAL_STATUS' => [
        'NONE' => 0,
        'ON' => 1,
        'DONE' => 2,
    ],
    'APPROVAL_RESULT' => [
        'OK' => 0,
        'NG' => 1,
    ],
    'RESULT_TYPE' => [
        'SUCCESS' => 0,
        'CONTACT' => 1,
        'RETURN' => 2,
        'ABORT' => 3,
    ],
    'ALLOCATION_METHOD' => [
        'EQUALITY' => 1,
        'BUSINESS_VOLUME' => 2,
        'LEARNING_LEVEL' => 3,
    ],
    'ALLOCATION_EVENNESS' => [
        'EVEN' => 1,
        'INDIVIDUAL' => 2,
    ],
    'ALLOCATION_STATUS' => [
        'NONE' => 0,
        'DONE' => 1,
    ],
    'DELIVERY_STATUS' => [
        'NONE' => 0,
        'DONE' => 1,
        'SCHEDULED' => 2,
    ],
    'APPROVAL_CONDITION' => [
        'FULL_MATCH' => 1,
    ],
    'STEP_TYPE' => [
        'INPUT' => 1,
        'APPROVAL' => 2,
    ],
    'TIME_UNIT' => [
        'MINUTE' => 1,
        'HOUR' => 2,
        'DAY' => 3,
    ],
    'REQUEST_STATUS' => [
        'ALL' => 0,
        'DOING' => 1,
        'FINISH' => 2,
        'EXCEPT' => 3,
    ],
    'IMPORTED_FILE_STATUS' => [
        'ALL' => 0,
        'DOING' => 1,
        'FINISH' => 2,
    ],
    'REQUEST_LOG_TYPE' => [
        /*
         * 取込関連 (1~20)
         */
        'IMPORT_COMPLETED' => 1,  // 取込完了

        /*
         * 割振関連 (21~40)
         */
        'ALLOCATION_COMPLETED' => 21,  // 割振完了
        'ALLOCATION_CHANGED' => 22,    // 割振変更

        /*
         * タスク関連 (41~60)
         */
        'TASK_COMPLETED_NORMALLY' => 41,   // タスク通常処理完了
        'TASK_COMPLETED_WITH_UNCLEAR_POINT' => 42,   // タスク不明処理（完了）
        'TASK_HOLDED_WITH_UNCLEAR_POINT' => 43,  // タスク不明処理（保留）
        'TASK_HOLDED_WITH_SOME_REASON' => 44,    // タスク保留
        'ALL_TASKS_COMPLETED' => 45,  // 全員のタスク完了

        /*
         * 承認関連 (61~80)
         */
        'APPROVAL_REJECTED' => 61,  // 承認却下
        'APPROVAL_ABORTED' => 62,  // 承認中止
        'APPROVAL_COMPLETED' => 63, // 承認完了

        /*
         * 納品関連 (81~100)
         */
        'DELIVERY_COMPLETED' => 81,  // 納品完了

        // 依頼全体の完了
        'ALL_COMPLETED' => 101,

        // 作業の戻し
        'STEPS_RETURNED' => 111,

        /*
         * 依頼削除、ステータス変更関連 (201~220)
         */
        'REQUEST_EXCEPTED' => 201,  // 除外処理
        'REQUEST_EXCEPTION_CANCELED' => 202,  // 除外の取り消し
        'REQUEST_HOLDED' => 203,  // 依頼保留処理
        'REQUEST_HOLD_RELEASED' => 204,  // 依頼保留状態解除

        /*
         * 補足情報関連 (221~240)
         */
        'ADDITIONAL_INFO_CREATED' => 221,  // 補足情報追加
        'ADDITIONAL_INFO_UPDATED' => 222,  // 補足情報編集
        'ADDITIONAL_INFO_DELETED' => 223,  // 補足情報削除
    ],
    'QUEUE_STATUS' => [
        'FAILURE' => -1,
        'PREVIOUS' => 0,
        'COMPLETED' => 1,
        'PROCESSING' => 99,
    ],
    'QUEUE_TYPE' => [
        'ALLOCATE' => 1,
        'APPROVE' => 2,
        'WORK_CREATE' => 3,
        'MAIL_SEND' => 4,
    ],
    'ITEM_TYPE' => [
        'STRING' => [
            'ID' => 1,
            'RULE' => 'string',
        ],
        'NUM' => [
            'ID' => 2,
            'RULE' => 'numeric',
        ],
        'DATE' => [
            'ID' => 3,
            'RULE' => 'date',
        ],
        'AMOUNT' => [
            'ID' => 4,
            'RULE' => 'string', // 仮
        ],
        'URL' => [
            'ID' => 5,
            'RULE' => 'string', // 仮
        ]
    ],
    'REQUEST_INFO_TYPE' => [
        'CLIENT_NAME' => 1,
        'DEADLINE' => 2,
    ],
    'CLIENT_ISSUE_TARGET_TYPE' => [
        'MAIL_FROM' => 1,
        'MAIL_CC' => 2,
        'MAIL_BCC' => 3,
    ],
    'DESTINATION_TYPE' => [
        0 => 'Local',
        1 => 'S3',
    ],
    'DIFF_CHECK_LEVEL' => [
        'NONE' => 0,
        'ERROR' => 1,
        'WARNING' => 2,
    ],
    'SORT_TYPE' => [
        'BINARY' => 'BINARY',
        'CHAR' => 'CHAR',
        'DATE' => 'DATE',
        'DATETIME' => 'DATETIME',
        'TIME' => 'TIME',
        'DECIMAL' => 'DECIMAL',
        'SIGNED' => 'SIGNED',
        'UNSIGNED' => 'UNSIGNED',
    ],
    'INPUT_RULE' => [
        'TEXT' => [
            'HALFWIDTH_ALPHANUMERIC_SYMBOL' => 1,
            'FULLWIDTH_ALPHANUMERIC_SYMBOL' => 2,
            'FULLWIDTH_HIRAGANA_KATAKANA' => 3,
        ]
    ],
    'MASTER_ID_PREFIX' => [
        'SEPARATOR' => '-',
        'TABLE_COLUMN' => [
            'r' => 'request_works.request_id',
            'w' => 'request_works.id',
        ],
        'REQUEST_ID' => 'r',
        'REQUEST_WORKS_ID' => 'w',
    ],
    'WORK_PROCESS_TYPE' =>[
    // 1: 取込
    'IMPORT' => 1,
    // 2: 割振
    'ALLOCATE' => 2,
    // 3: タスク
    'TASK' => 3,
    // 4: 承認
    'APPROVE' => 4,
    // 5: 納品
    'DELIVERY' => 5,
    // 6: 管理
    'MANAGE' => 6,
    // 7: マスタ設定
    'MASTER_SET' => 7,
    // 8: クライアント確認
    'CLIENT_CHECK' => 8,
    // 9: 検証
    'VERIFICATION' => 9,
    // 99: 他
    'OTHER' => 99,
    ],

    'STEP_MODE_TYPE' =>[
        'MANUAL' => 0,
        'AUTO' => 1,
    ],
    'APPENDICES_TYPE' => [
        'RELATED_MAIL' => 1, // 関連メール
        'ADDITIONAL' => 2, // 補足情報
        'REQUEST_LOG' => 3, // 依頼ログ
    ],
    'SEX' => [
        'MALE' => 1,
        'FEMALE' => 2,
    ],
    'FILE_IMPORT_TYPE' => [
        'NEW' => 0,
        'ADD' => 1,
    ],
    'DISPLAY_MODE' => [
        'READ_ONLY' => 0,
        'EDIT_ON' => 1,
        'EDIT_OFF' => 2,
    ],
    "ADD_USER" => [
        "BUSINESS_ADMIN" => 1,
        "BUSINESS_OPERATOR" => 2,
        "WORKS_USER" => 3,
        "EDUCATIONAL_WORKS_USER" => 4
    ],
    'DISPLAY_FORMAT' => [
        'NUM_TREE_DIGITS_COMMA_DELIMITED' => 1
    ]
];

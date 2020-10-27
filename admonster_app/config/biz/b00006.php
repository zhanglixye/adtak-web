<?php
// 経費申請業務用config

return [
    'IMAGE_SET' => [
        'IMAGE_X' => 187,
        'IMAGE_Y' => 30,
        'IMAGE_W' => 10,
        'IMAGE_DIR' => storage_path('biz/b00006/workplace.png'),
        'SET_OUTPUT_DIR' => storage_path('biz/b00006/')
    ],

    'REPORT_TEMPLATE_FILE_DIR' => storage_path('biz/b00006/template.xlsx'),
    'REPORT_TEMPLATE_DIR' => storage_path('biz/b00006/'),
    'MAIL_CONFIG' => [
        's00011' => [
            // 邮件页面标识(用于在一个业务有多个页面时候定位页面使用，对应于jsonfile的lastDisplayedPage）
            'MAIL_PAGE_ID' => '3',

            // 客户端上传的邮件附件在请求报文中的key路径，多级路径定义为数组的多个元素
            'MAIL_CLIENT_UPLOAD_ATTACH_KEY' => ['uploadFiles'],
            // 客户端上传的作业时间在请求报文中的key路径，多级路径定义为数组的多个元素
            'MAIL_CLIENT_TIME_MAIN' => ['G00000_35'],
            // 客户端上传的作业开始时间在请求报文中的key路径(相对于MAIL_CLIENT_TIME_MAIN)
            'MAIL_CLIENT_TIME_START_AT' => 'C00700_36',
            // 客户端上传的作业完成时间在请求报文中的key路径(相对于MAIL_CLIENT_TIME_MAIN)
            'MAIL_CLIENT_TIME_FINISHED_AT' => 'C00700_37',
            // 客户端上传的作业总时间在请求报文中的key路径(相对于MAIL_CLIENT_TIME_MAIN)
            'MAIL_CLIENT_TIME_TOTAL' => 'C00100_38',

            // 邮件在作业实绩JsonFile中的根路径
            'MAIL_CONTENT_MAIN' => ['G00000_27'],
            // MAIL_TO在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素
            'MAIL_CONTENT_TO' => ['C00300_28'],
            // 邮件cc在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素
            'MAIL_CONTENT_CC' => ['C00300_29'],
            // 邮件标题在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素
            'MAIL_CONTENT_SUBJECT' => ['C00100_30'],
            // 邮件本文在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素
            'MAIL_CONTENT_BODY' => ['C00900_31'],
            // 邮件附件在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素
            'MAIL_CONTENT_ATTACH_KEY' => ['C00800_32'],
            // 作業内容にCHECK_LIST在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素
            'MAIL_CONTENT_CHECK_LIST' => ['G00000_33'],
            // 「不明あり」で処理します,担当者へのコメント在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素
            'MAIL_CONTENT_UNKNOWN' => ['C00200_34'],


            // 其它页面的附件在作业实绩JsonFile中的key路径，多级路径定义为数组的多个元素
            'MAIL_OTHER_PAGE_ATTACH_KEY' => [
                //交通費AP PDF
                ['C00800_24','C00800_25'],
                //交通費常驻 PDF
                ['C00800_24','C00800_26'],
            ],

            //保存邮件后返回的客户端的附件列表
            'MAIL_RETURN_ATTACH_KEY' => [
                //返回的Key和其对应的附件文件在作业实绩JsonFile中的Key路径
                'C00800_25_uploadFiles' => ['C00800_24','C00800_25'],
                'C00800_26_uploadFiles' => ['C00800_24','C00800_26'],
                'uploadFiles' => ['G00000_27','C00800_32'],
            ],

            //邮件模板设置
            'MAIL_REPLAY_BODY_TEMPLATE' => [
                'step_id' => 11,
                'condition_cd' => 1,
                'default_mail_to' => ['yamasaki@adpro-inc.co.jp']
            ],
        ],

        's00012' => [
            // 邮件页面标识(用于在一个业务有多个页面时候定位页面使用，对应于jsonfile的lastDisplayedPage）
            'MAIL_PAGE_ID' => '3',

            // 客户端上传的邮件附件在请求报文中的key路径，多级路径定义为数组的多个元素
            'MAIL_CLIENT_UPLOAD_ATTACH_KEY' => ['uploadFiles'],
            // 客户端上传的作业时间在请求报文中的key路径，多级路径定义为数组的多个元素
            'MAIL_CLIENT_TIME_MAIN' => ['G00000_35'],
            // 客户端上传的作业开始时间在请求报文中的key路径(相对于MAIL_CLIENT_TIME_MAIN)
            'MAIL_CLIENT_TIME_START_AT' => 'C00700_36',
            // 客户端上传的作业完成时间在请求报文中的key路径(相对于MAIL_CLIENT_TIME_MAIN)
            'MAIL_CLIENT_TIME_FINISHED_AT' => 'C00700_37',
            // 客户端上传的作业总时间在请求报文中的key路径(相对于MAIL_CLIENT_TIME_MAIN)
            'MAIL_CLIENT_TIME_TOTAL' => 'C00100_38',

            // 邮件在作业实绩JsonFile中的根路径
            'MAIL_CONTENT_MAIN' => ['G00000_27'],
            // MAIL_TO在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素
            'MAIL_CONTENT_TO' => ['C00300_28'],
            // 邮件cc在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素
            'MAIL_CONTENT_CC' => ['C00300_29'],
            // 邮件标题在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素
            'MAIL_CONTENT_SUBJECT' => ['C00100_30'],
            // 邮件本文在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素
            'MAIL_CONTENT_BODY' => ['C00900_31'],
            // 邮件附件在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素
            'MAIL_CONTENT_ATTACH_KEY' => ['C00800_32'],
            // 作業内容にCHECK_LIST在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素
            'MAIL_CONTENT_CHECK_LIST' => ['G00000_33'],
            // 「不明あり」で処理します,担当者へのコメント在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素
            'MAIL_CONTENT_UNKNOWN' => ['C00200_34'],


            // 其它页面的附件在作业实绩JsonFile中的key路径，多级路径定义为数组的多个元素
            'MAIL_OTHER_PAGE_ATTACH_KEY' => [
                //承认前的『★交通費(AP) メール』 シートFile List
                ['C00800_1'],
                //承认前的『★交通費(常駐)メール』シート File List
                ['C00800_2'],
                //承认后的『★交通費(AP) メール』 シートFile List
                ['C00800_3'],
                //承认后的『★交通費(常駐)メール』シート File List
                ['C00800_4'],
            ],

            //保存邮件后返回的客户端的附件列表
            'MAIL_RETURN_ATTACH_KEY' => [
                //返回的Key和其对应的附件文件在作业实绩JsonFile中的Key路径
                'uploadFiles' => ['G00000_27','C00800_32'],
            ],

            //邮件模板设置
            'MAIL_REPLAY_BODY_TEMPLATE' => [
                'step_id' => 12,
                'condition_cd' => 1,
                'default_mail_to' => ['ad-takt-keihi@adpro-inc.co.jp']
            ],
        ],
    ],
    'MAIL_SETTING' => [
        'contact_mail_receiver' => env('APP_ENV') == 'production'? 'ad-takt-keihi@adpro-inc.co.jp': 'support@kaiwait.com',
    ],
];

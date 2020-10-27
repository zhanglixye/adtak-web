<?php

/*
|--------------------------------------------------------------------------
| アド・プロ 共通のメール 業務用
|--------------------------------------------------------------------------
|
*/

/* -------------------- generate from artisan make:auth ------------------------- */
Route::group(['middleware' => 'auth'], function () {
    Route::group(['prefix' => 'api', 'as' => 'api', 'namespace' => 'Api'], function () {
        // 業務
        Route::group(['prefix' => 'biz', 'as' => 'biz', 'namespace' => 'Biz'], function () {
            Route::group([ 'prefix' => 'common', 'as' => '.common', 'namespace' => 'Common' ], function () {
                // CommonMailSetting
                Route::group([ 'prefix' => 'mail', 'as' => '.mail'], function () {
                    //---------------------------邮件-----------------------------------
                    // 最新の作業履歴
                    Route::post('getTaskResult', ['uses' => 'MailController@getTaskResult'])->name('.getTaskResult');
                    // 获取默认的mailSetting
                    Route::post('getCommonMailSettingByTaskId', ['uses' => 'MailController@getCommonMailSettingByTaskId'])->name('.getCommonMailSettingByTaskId');
                    // 取得默认的收件人
                    Route::post('getDefaultMailTo', ['uses' => 'MailController@getDefaultMailTo'])->name('.getDefaultMailTo');
                    // 取得默认的抄送人
                    Route::post('getDefaultMailCc', ['uses' => 'MailController@getDefaultMailCc'])->name('.getDefaultMailCc');
                    // 获取默认的标题
                    Route::post('getDefaultSubject', ['uses' => 'MailController@getDefaultSubject'])->name('.getDefaultSubject');
                    // 获取默认的本文
                    Route::post('getDefaultBody', ['uses' => 'MailController@getDefaultBody'])->name('.getDefaultBody');
                    // 获取默认的不明
                    Route::post('getDefaultUnknown', ['uses' => 'MailController@getDefaultUnknown'])->name('.getDefaultUnknown');
                    // 获取默认的作業時間
                    Route::post('getDefaultUseTime', ['uses' => 'MailController@getDefaultUseTime'])->name('.getDefaultUseTime');
                    // 获取チェックリスト項目
                    Route::post('getDefaultChecklistValues', ['uses' => 'MailController@getDefaultChecklistValues'])->name('.getDefaultChecklistValues');
                    // 获取default template
                    Route::post('getDefaultBodyTemplates', ['uses' => 'MailController@getDefaultBodyTemplates'])->name('.getDefaultBodyTemplates');
                    // 获取template
                    Route::post('getBodyTemplates', ['uses' => 'MailController@getBodyTemplates'])->name('.getBodyTemplates');
                    // 获取default署名テンプレート
                    Route::post('getDefaultSignTemplates', ['uses' => 'MailController@getDefaultSignTemplates'])->name('.getDefaultSignTemplates');
                    // 获取署名テンプレート
                    Route::post('getSignTemplates', ['uses' => 'MailController@getSignTemplates'])->name('.getSignTemplates');
                    // 获取default添付メール
                    Route::post('getDefaultAttachments', ['uses' => 'MailController@getDefaultAttachments'])->name('.getDefaultAttachments');
                    // 获取チェックリスト項目
                    Route::post('getChecklistItems', ['uses' => 'MailController@getChecklistItems'])->name('.getChecklistItems');
                    // 获取MailTo頻度
                    Route::post('searchMailToFrequencyList', ['uses' => 'MailController@searchMailToFrequencyList'])->name('.searchMailToFrequencyList');
                    // 获取MailCc頻度
                    Route::post('searchMailCcFrequencyList', ['uses' => 'MailController@searchMailCcFrequencyList'])->name('.searchMailCcFrequencyList');
                    // 临时保存邮件
                    Route::post('tmpSaveMail', ['uses' => 'MailController@tmpSaveMail'])->name('.tmpSaveMail');
                    // 保存邮件
                    Route::post('saveMail', ['uses' => 'MailController@saveMail'])->name('.saveMail');
                    // 有效性校验
                    Route::post('inputValidation', ['uses' => 'MailController@inputValidation'])->name('.inputValidation');
                    // 保存署名テンプレート
                    Route::post('saveSignTemplates', ['uses' => 'MailController@saveSignTemplates'])->name('.saveSignTemplates');
                    // 保存本文テンプレート
                    Route::post('saveBodyTemplates', ['uses' => 'MailController@saveBodyTemplates'])->name('.saveBodyTemplates');
                    // 下载文件
                    Route::post('downloadFile', ['uses' => 'MailController@downloadFile'])->name('.downloadFile');
                    // 下载文件
                    Route::get('/{request_work_id}/{task_id}/downloadPdf', ['uses' => 'MailController@downloadPdf'])->name('.downloadPdf');
                    //获取作业时间
                    Route::post('getWorktime', ['uses' => 'MailController@getWorktime'])->name('.getWorktime');
                    // 取得附件允许的文件类型列表
                    Route::post('getCommonMailAttachmentTypeByTaskId', ['uses' => 'MailController@getCommonMailAttachmentTypeByTaskId'])->name('.getCommonMailAttachmentTypeByTaskId');
                });
            });
        });
    });
});

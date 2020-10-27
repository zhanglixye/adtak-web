<?php

/*
|--------------------------------------------------------------------------
| アド・プロ経費申請業務用
|--------------------------------------------------------------------------
|
*/

/* -------------------- generate from artisan make:auth ------------------------- */

Route::group(['middleware' => 'auth'], function () {


    /* -------------------- api ------------------------- */

    Route::group(['prefix' => 'api', 'as' => 'api', 'namespace' => 'Api'], function () {
        // 業務
        Route::group(['prefix' => 'biz', 'as' => 'biz', 'namespace' => 'Biz'], function () {
            Route::group([ 'prefix' => 'b00006', 'as' => '.b00006', 'namespace' => 'B00006' ], function () {
                // 経費：月次レポート
                Route::get('keihiMonthlyReport', ['uses' => 'B00006Controller@keihiMonthlyReport'])->name('.keihiMonthlyReport');
                // CommonMailSetting
                Route::group([ 'prefix' => 's00011', 'as' => '.s00011'], function () {
                    // init
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'S00011Controller@create'])->name('.create');
                    //------------------------经费录入----------------------------------
                    // 従業員情報 自动匹配
                    Route::post('searchEmployees', ['uses' => 'S00011Controller@searchEmployees'])->name('.searchEmployees');
                    // 临时保存经费数据
                    Route::post('tmpSaveExpenseData', ['uses' => 'S00011Controller@tmpSaveExpenseData'])->name('.tmpSaveExpenseData');
                    // 保存经费数据
                    Route::post('saveExpenseData', ['uses' => 'S00011Controller@saveExpenseData'])->name('.saveExpenseData');
                    //--------------------------pdf上传---------------------------------
                    // 交通費PDF upload
                    Route::post('uploadPdf', ['uses' => 'S00011Controller@uploadPdf'])->name('.uploadPdf');
                    //---------------------------邮件-----------------------------------
                    // 最新の作業履歴
                    Route::post('getTaskResult', ['uses' => 'S00011Controller@getTaskResult'])->name('.getTaskResult');
                    // 获取默认的mailSetting
                    Route::post('getCommonMailSettingByTaskId', ['uses' => 'S00011Controller@getCommonMailSettingByTaskId'])->name('.getCommonMailSettingByTaskId');
                    // 取得默认的收件人
                    Route::post('getDefaultMailTo', ['uses' => 'S00011Controller@getDefaultMailTo'])->name('.getDefaultMailTo');
                    // 取得默认的抄送人
                    Route::post('getDefaultMailCc', ['uses' => 'S00011Controller@getDefaultMailCc'])->name('.getDefaultMailCc');
                    // 获取默认的标题
                    Route::post('getDefaultSubject', ['uses' => 'S00011Controller@getDefaultSubject'])->name('.getDefaultSubject');
                    // 获取默认的本文
                    Route::post('getDefaultBody', ['uses' => 'S00011Controller@getDefaultBody'])->name('.getDefaultBody');
                    // 获取默认的不明
                    Route::post('getDefaultUnknown', ['uses' => 'S00011Controller@getDefaultUnknown'])->name('.getDefaultUnknown');
                    // 获取默认的作業時間
                    Route::post('getDefaultUseTime', ['uses' => 'S00011Controller@getDefaultUseTime'])->name('.getDefaultUseTime');
                    // 获取チェックリスト項目
                    Route::post('getDefaultChecklistValues', ['uses' => 'S00011Controller@getDefaultChecklistValues'])->name('.getDefaultChecklistValues');
                    // 获取default template
                    Route::post('getDefaultBodyTemplates', ['uses' => 'S00011Controller@getDefaultBodyTemplates'])->name('.getDefaultBodyTemplates');
                    // 获取template
                    Route::post('getBodyTemplates', ['uses' => 'S00011Controller@getBodyTemplates'])->name('.getBodyTemplates');
                    // 获取default署名テンプレート
                    Route::post('getDefaultSignTemplates', ['uses' => 'S00011Controller@getDefaultSignTemplates'])->name('.getDefaultSignTemplates');
                    // 获取署名テンプレート
                    Route::post('getSignTemplates', ['uses' => 'S00011Controller@getSignTemplates'])->name('.getSignTemplates');
                    // 获取default添付メール
                    Route::post('getDefaultAttachments', ['uses' => 'S00011Controller@getDefaultAttachments'])->name('.getDefaultAttachments');
                    // 获取チェックリスト項目
                    Route::post('getChecklistItems', ['uses' => 'S00011Controller@getChecklistItems'])->name('.getChecklistItems');
                    // 获取MailTo頻度
                    Route::post('searchMailToFrequencyList', ['uses' => 'S00011Controller@searchMailToFrequencyList'])->name('.searchMailToFrequencyList');
                    // 获取MailCc頻度
                    Route::post('searchMailCcFrequencyList', ['uses' => 'S00011Controller@searchMailCcFrequencyList'])->name('.searchMailCcFrequencyList');
                    // 临时保存邮件
                    Route::post('tmpSaveMail', ['uses' => 'S00011Controller@tmpSaveMail'])->name('.tmpSaveMail');
                    // 保存邮件
                    Route::post('saveMail', ['uses' => 'S00011Controller@saveMail'])->name('.saveMail');
                    // 保存署名テンプレート
                    Route::post('saveSignTemplates', ['uses' => 'S00011Controller@saveSignTemplates'])->name('.saveSignTemplates');
                    // 保存本文テンプレート
                    Route::post('saveBodyTemplates', ['uses' => 'S00011Controller@saveBodyTemplates'])->name('.saveBodyTemplates');
                    // 下载文件
                    Route::post('downloadFile', ['uses' => 'S00011Controller@downloadFile'])->name('.downloadFile');
                    // 下载文件
                    Route::get('downloadPdf', ['uses' => 'S00011Controller@downloadPdf'])->name('.downloadPdf');
                });
                // 経費承认
                Route::group([
                    'prefix' => 's00012',
                    'as' => '.s00012',
                ], function () {
                    // 承认画面
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'S00012Controller@create'])->name('.create');
                    // 承认
                    Route::post('approval', ['uses' => 'S00012Controller@approval'])->name('.approval');
                    // 拒绝
                    Route::post('reject', ['uses' => 'S00012Controller@reject'])->name('.reject');
                    // 临时保存
                    Route::post('tmpSave', ['uses' => 'S00012Controller@tmpSave'])->name('.tmpSave');
                    // 保存
                    Route::post('done', ['uses' => 'S00012Controller@done'])->name('.done');
                });
            });
        });
    });
});

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
                Route::group([ 'prefix' => 'dynamic_page', 'as' => '.DynamicPage'], function () {
                    //---------------------------dynamic_page-----------------------------------
                    // uploadFileToS3
                    Route::post('uploadFileToS3', ['uses' => 'DynamicPageController@uploadFileToS3'])->name('.uploadFileToS3');
                    // 保留
                    Route::post('tmpSave', ['uses' => 'DynamicPageController@tmpSave'])->name('.tmpSave');
                    // 处理する
                    Route::post('process', ['uses' => 'DynamicPageController@process'])->name('.process');
                    // 不明処理
                    Route::post('unknown', ['uses' => 'DynamicPageController@unknown'])->name('.unknown');
                    // 添付ファイルに関する情報を取得する
                    Route::post('getTaskResultFileInfoById', ['uses' => 'DynamicPageController@getTaskResultFileInfoById'])->name('.getTaskResultFileInfoById');
                });
            });
        });
    });
});

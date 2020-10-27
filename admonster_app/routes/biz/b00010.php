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
            Route::group([ 'prefix' => 'b00010', 'as' => '.b00010', 'namespace' => 'B00010' ], function () {
                Route::group([
                    'prefix' => 's00016',
                    'as' => '.s00016',
                ], function () {
                    // 承认画面
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'S00016Controller@create'])->name('.create');
                    // 临时保存
                    Route::post('tmpSave', ['uses' => 'S00016Controller@tmpSave'])->name('.tmpSave');
                    // 押印
                    Route::post('approvalPdf', ['uses' => 'S00016Controller@approvalPdf'])->name('.approvalPdf');
                    // 获取承认用的图章
                    Route::get('/{request_work_id}/{task_id}/getSeal', ['uses' => 'S00016Controller@getSeal'])->name('.getSeal');
                    // 清除押印后的PDF
                    Route::post('eraseStamp', ['uses' => 'S00016Controller@eraseStamp'])->name('.eraseStamp');
                });
            });
        });
    });
});

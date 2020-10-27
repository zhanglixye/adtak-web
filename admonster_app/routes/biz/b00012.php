<?php

/*
|--------------------------------------------------------------------------
| アド・プロ長春ルーチン業務用
|--------------------------------------------------------------------------
|
*/

/* -------------------- generate from artisan make:auth ------------------------- */

Route::group(['middleware' => 'auth'], function () {


    /* -------------------- api ------------------------- */

    Route::group(['prefix' => 'api', 'as' => 'api', 'namespace' => 'Api'], function () {
        // 業務
        Route::group(['prefix' => 'biz', 'as' => 'biz', 'namespace' => 'Biz'], function () {
            Route::group([ 'prefix' => 'b00012', 'as' => '.b00012', 'namespace' => 'B00012' ], function () {
                // AG業務件数レポート（日本）
                Route::get('agRegisterReport', ['uses' => 'B00012Controller@agRegisterReport'])->name('.agRegisterReport');
                //
                Route::group([ 'prefix' => 's00018', 'as' => '.s00018'], function () {
                    // init
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'S00018Controller@create'])->name('.create');
                });
            });
        });
    });
});

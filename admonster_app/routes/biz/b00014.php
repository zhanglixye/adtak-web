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
            Route::group([ 'prefix' => 'b00014', 'as' => '.b00014', 'namespace' => 'B00014' ], function () {
                // AG業務件数レポート（長春）
                Route::get('agRegisterReport', ['uses' => 'B00014Controller@agRegisterReport'])->name('.agRegisterReport');
                //
                Route::group([ 'prefix' => 's00020', 'as' => '.s00020'], function () {
                    // init
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'S00020Controller@create'])->name('.create');
                });
            });
        });
    });
});

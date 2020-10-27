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
            Route::group([ 'prefix' => 'b00008', 'as' => '.b00008', 'namespace' => 'B00008' ], function () {
                //
                Route::group([ 'prefix' => 's00014', 'as' => '.s00014'], function () {
                    // init
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'S00014Controller@create'])->name('.create');
                });
            });
        });
    });
});

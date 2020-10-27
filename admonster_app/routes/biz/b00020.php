<?php

/*
|--------------------------------------------------------------------------
| XOneチームトライアル_01
|--------------------------------------------------------------------------
|
*/

/* -------------------- generate from artisan make:auth ------------------------- */

Route::group(['middleware' => 'auth'], function () {


    /* -------------------- api ------------------------- */

    Route::group(['prefix' => 'api', 'as' => 'api', 'namespace' => 'Api'], function () {
        // 業務
        Route::group(['prefix' => 'biz', 'as' => 'biz', 'namespace' => 'Biz'], function () {
            Route::group([ 'prefix' => 'b00020', 'as' => '.b00020', 'namespace' => 'B00020' ], function () {
                //
                Route::group([ 'prefix' => 's00026', 'as' => '.s00026'], function () {
                    // init
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'S00026Controller@create'])->name('.create');
                });
            });
        });
    });
});

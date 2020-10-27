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
            Route::group([ 'prefix' => 'b00019', 'as' => '.b00019', 'namespace' => 'B00019' ], function () {
                //
                Route::group([ 'prefix' => 's00025', 'as' => '.s00025'], function () {
                    // init
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'S00025Controller@create'])->name('.create');
                });
            });
        });
    });
});

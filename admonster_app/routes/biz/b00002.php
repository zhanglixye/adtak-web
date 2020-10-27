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
            Route::group([
                'prefix' => 'b00002',
                'as' => '.b00002',
                'namespace' => 'B00002'
            ], function () {
                // 経費承認
                Route::group([
                    'prefix' => 's00007',
                    'as' => '.s00007',
                ], function () {
                    // 承認画面
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'S00007Controller@create'])->name('.create');
                    // データ登録
                    Route::post('store', ['uses' => 'S00007Controller@store'])->name('.store');
                });
            });
        });
    });
});

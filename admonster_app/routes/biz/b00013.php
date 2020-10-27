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
            Route::group([ 'prefix' => 'b00013', 'as' => '.b00013', 'namespace' => 'B00013' ], function () {
                //
                Route::group([ 'prefix' => 's00019', 'as' => '.s00019'], function () {
                    // init
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'S00019Controller@create'])->name('.create');
                    // 一時保存（対応中）
                    Route::post('hold', ['uses' => 'S00019Controller@hold'])->name('.hold');
                    // 確認
                    Route::post('done', ['uses' => 'S00019Controller@done'])->name('.done');
                    // 問い合わせ（不明あり）
                    Route::post('makeContact', ['uses' => 'S00019Controller@makeContact'])->name('.makeContact');
                });
            });
        });
    });
});

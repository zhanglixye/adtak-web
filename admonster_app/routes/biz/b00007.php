<?php

/*
|--------------------------------------------------------------------------
| アド・プロAbbeyチェック業務用
|--------------------------------------------------------------------------
|
*/

/* -------------------- generate from artisan make:auth ------------------------- */

Route::group(['middleware' => 'auth'], function () {


    /* -------------------- api ------------------------- */

    Route::group(['prefix' => 'api', 'as' => 'api', 'namespace' => 'Api'], function () {
        // 業務
        Route::group(['prefix' => 'biz', 'as' => 'biz', 'namespace' => 'Biz'], function () {
            Route::group([ 'prefix' => 'b00007', 'as' => '.b00007', 'namespace' => 'B00007' ], function () {
                //
                Route::group([ 'prefix' => 's00013', 'as' => '.s00013'], function () {
                    // init
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'S00013Controller@create'])->name('.create');
                    // 素材 結果キャプチャー ダウンロード
                    Route::post('downloadFile', ['uses' => 'S00013Controller@downloadFromS3'])->name('.downloadFile');
                    // 素材 結果キャプチャー アップロード
                    Route::post('uploadFile', ['uses' => 'S00013Controller@uploadFileToS3'])->name('.uploadFile');
                    // 素材 暗号化された圧縮ファイルを解凍
                    Route::post('unZipMaterialWithPasswd', ['uses' => 'S00013Controller@unZipMaterialWithPasswd'])->name('.unZipMaterialWithPasswd');
                    // 素材全件ダウンロード
                    Route::post('/{request_work_id}/{task_id}/materialZipDownload', ['uses' => 'S00013Controller@materialZipDownload'])->name('.materialZipDownload');
                    // データベース検索
                    Route::post('search', ['uses' => 'S00013Controller@search'])->name('.search');
                    // 処理する
                    Route::post('commitWork', ['uses' => 'S00013Controller@commitWork'])->name('.commitWork');
                    // 保留
                    Route::post('saveWork', ['uses' => 'S00013Controller@saveWork'])->name('.saveWork');
                    // 不明あり
                    Route::post('wrongWork', ['uses' => 'S00013Controller@wrongWork'])->name('.wrongWork');
                });
            });
        });
    });
});

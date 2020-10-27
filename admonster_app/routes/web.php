<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/healthCheck', function () {
    config()->set('session.driver', 'array');
    return response('Hello World', 200)
        ->header('Content-Type', 'text/plain');
});

/* -------------------- generate from artisan make:auth ------------------------- */

Auth::routes();

// ゲストクライアント認証用
Route::group(['prefix' => 'guest_client', 'middleware' => 'guest:guest_client'], function () {
    Route::get('issue_password', 'GuestClient\Auth\LoginController@showIssuePasswordForm')->name('guest_client.issue_password');
    Route::post('issue_password', 'GuestClient\Auth\LoginController@issuePassword')->name('guest_client.issue_password');
    Route::get('login', 'GuestClient\Auth\LoginController@showLoginForm')->name('guest_client.login');
    Route::post('login', 'GuestClient\Auth\LoginController@login')->name('guest_client.login');
});

Route::get('/home', 'HomeController@index')->name('home');

// 共通
Route::group(['prefix' => 'utilities', 'as' => 'utilities'], function () {
    // ファイルダウンロード
    Route::get('download_allow_file', 'UtilitiesController@downloadFile')->name('.downloadFile');
});

Route::group(['middleware' => 'auth'], function () {
    /**
     *  ※ routes/api.php に書くと認証が面倒 + not public api なので、こっちに書く。
     */
    /* -------------------- api ------------------------- */
    Route::group(['prefix' => 'api', 'as' => 'api', 'namespace' => 'Api'], function () {
        // 共通機能
        Route::group(['prefix' => 'utilities', 'as' => 'utilities'], function () {
            // S3からファイル情報を取得
            Route::post('/fileInfoFromS3', 'UtilitiesController@fileInfoFromS3')->name('.fileInfoFromS3');
            // ファイル参照URLを取得（オリジナル）
            Route::get('/getFileReferenceUrlForOriginal', 'UtilitiesController@getFileReferenceUrlForOriginal')->name('.getFileReferenceUrlForOriginal');
            // ファイル参照URLを取得（サムネイル）
            Route::get('/getFileReferenceUrlForThumbnail', 'UtilitiesController@getFileReferenceUrlForThumbnail')->name('.getFileReferenceUrlForThumbnail');
            // ファイルプレビュー用の一時認証URLを取得
            Route::post('/getFilePreviewUrl', 'UtilitiesController@getFilePreviewUrl')->name('.getFilePreviewUrl');
            // S3からダウンロード
            Route::get('/downloadFromS3', 'UtilitiesController@downloadFromS3')->name('.downloadFromS3');
            // ローカルからダウンロード
            Route::get('/downloadFromLocal', 'UtilitiesController@downloadFromLocal')->name('.downloadFromLocal');
            // zipファイル作成
            Route::post('createZipFile', 'UtilitiesController@createZipFile')->name('.createZipFile');
            // サイドメニューに表示する情報を取得
            Route::get('/getSideMenuContents', 'UtilitiesController@getSideMenuContents')->name('.getSideMenuContents');
            // メール作成
            Route::post('/create_send_mail', 'UtilitiesController@createSendMail')->name('.create_send_mail');
        });

        Route::group(['prefix' => 'user', 'as' => 'user', 'namespace' => 'User'], function () {
            // 一覧
            Route::get('', 'UsersController@index')->name('.index');
            // 画像を更新
            Route::post('/updateUserImage', 'UsersController@updateUserImage')->name('.updateUserImage');
            // パスワードを更新
            Route::post('/updatePassword', 'UsersController@updatePassword')->name('.updatePassword');
            // プロフィール情報を更新
            Route::post('/updateProfile', 'UsersController@updateProfile')->name('.updateProfile');
        });

        // マスタ
        Route::group(['prefix' => 'master', 'as' => 'master', 'namespace' => 'Master'], function () {
            // 作業マスタ
            Route::group(['prefix' => 'steps', 'as' => 'steps'], function () {
                // 一覧
                Route::get('', 'StepsController@index')->name('.index');
                // 詳細
                Route::get('/getItemConfigs', 'StepsController@getItemConfigs')->name('.getItemConfigs');
                // 更新
                Route::post('/updateRequestTemplate', 'StepsController@updateRequestTemplate')->name('.updateRequestTemplate');
            });
            // 業務マスタ
            Route::group(['prefix' => 'businesses', 'as' => 'businesses'], function () {
                // 一覧
                Route::get('', 'BusinessesController@index')->name('.index');
                // 詳細
                Route::post('/detail', 'BusinessesController@detail')->name('.detail');
                // 作業候補者を削除
                Route::post('/deleteCandidates', 'BusinessesController@deleteCandidates')->name('.deleteCandidates');
                // 作業候補者を追加
                Route::post('/addCandidates', 'BusinessesController@addCandidates')->name('.addCandidates');
                // 管理者を追加
                Route::post('/addAdministrators', 'BusinessesController@addAdministrators')->name('.addAdministrators');
                // 管理者を削除
                Route::post('/deleteAdministrators', 'BusinessesController@deleteAdministrators')->name('.deleteAdministrators');
                // 担当者を追加
                Route::post('/addWorksUsers', 'BusinessesController@addWorksUsers')->name('.addWorksUsers');
                // 担当者を削除
                Route::post('/deleteWorksUser', 'BusinessesController@deleteWorksUser')->name('.deleteWorksUser');
                // 演習担当者を追加
                Route::post('/addEducationalWorksUsers', 'BusinessesController@addEducationalWorksUsers')->name('.addEducationalWorksUsers');
                // 演習担当者を削除
                Route::post('/deleteEducationalWorksUser', 'BusinessesController@deleteEducationalWorksUser')->name('.deleteEducationalWorksUser');
                // 業務作業候補者を各作業の作業者と各作業の教育担当者にコピー
                Route::post('/copy', 'BusinessesController@copy')->name('.copy');
            });
            // ユーザーマスタ
            Route::group(['prefix' => 'users', 'as' => 'users'], function () {
                // 一覧
                Route::post('', 'UsersController@index')->name('.index');
                // ユーザーを追加
                Route::post('/addUser', 'UsersController@addUser')->name('.addUser');
                // passwordを更新
                Route::post('/updatePassword', 'UsersController@updatePassword')->name('.updatePassword');
            });
        });

        // タスク
        Route::group(['prefix' => 'tasks', 'as' => 'tasks'], function () {
            // 一覧
            Route::post('', 'TasksController@index')->name('.index');
        });

        // 検証
        Route::group(['prefix' => 'verification', 'as' => 'verification'], function () {
            // 詳細
            Route::post('/edit', 'VerificationController@edit')->name('.edit');
            // 保存
            Route::post('/store', 'VerificationController@store')->name('.store');
        });

        // 業務
        Route::group(['prefix' => 'businesses', 'as' => 'businesses'], function () {
            // 一覧
            Route::post('', 'BusinessesController@index')->name('.index');
            // 詳細
            Route::post('/detail', 'BusinessesController@show')->name('.show');
            // 登録
            Route::post('/store', 'BusinessesController@store')->name('.store');
            // 自分が管理者の一覧
            Route::get('/managed_list', 'BusinessesController@managedList')->name('.managed_list');
        });

        // 業務状況
        Route::group(['prefix' => 'business_states', 'as' => 'business_states'], function () {
            // 一覧
            Route::post('', 'BusinessStatesController@index')->name('.index');
        });

        // 依頼作業
        Route::group(['prefix' => 'request_works', 'as' => 'request_works'], function () {
            // 一覧
            Route::post('', 'RequestWorksController@index')->name('.index');
            // 詳細
            Route::post('/show', 'RequestWorksController@show')->name('.show');
        });
        // 依頼
        Route::group(['prefix' => 'requests', 'as' => 'requests'], function () {
            // 一覧
            Route::post('', 'RequestsController@index')->name('.index');
            // 詳細 => クライアントにも提供するため、別のルートグループ内に定義
            // 更新
            Route::post('/update', 'RequestsController@update')->name('.update');
            // 削除
            Route::post('/delete', 'RequestsController@delete')->name('.delete');
            // 複製
            Route::post('/replicate', 'RequestsController@replicateRequest')->name('.replicate');
            // 依頼関連メール送信先メールアドレス生成
            Route::post('/alias_mail_address', 'RequestsController@aliasMailAddress')->name('.alias_mail_address');
            // ゲストクライアントアカウント発行
            Route::post('/issue_guest_client_account', 'RequestsController@issueGuestClientAccount')->name('.issue_guest_client_account');
            Route::post('/get_guest_client_account_issue_status', 'RequestsController@getGuestClientAccountIssueStatus')->name('.get_guest_client_account_issue_status');
            // 納品期限の変更
            Route::post('/changeDeliveryDeadline', 'RequestsController@changeDeliveryDeadline')->name('.changeDeliveryDeadline');
            // 依頼関連メール
            Route::group(['prefix' => 'request_related_mails', 'as' => 'request_related_mails'], function () {
                // リスト
                Route::post('', 'RequestsController@relatedMailList')->name('.list');
                // 更新
                Route::post('update', 'RequestsController@updateRelatedMail')->name('.update');
                // 削除
                Route::post('delete', 'RequestsController@deletRelatedMail')->name('.delete');
            });
            // 依頼メール詳細
            Route::post('/getRequestMailDetail', 'RequestsController@getRequestMailDetail')->name('.getRequestMailDetail');
        });

        // 割振(教育)
        Route::group(['prefix' => 'education', 'as' => 'allocations'], function () {
            // 一覧取得
            Route::post('/allocations/index', 'EducationAllocationsController@index')->name('.index');
            // 初期表示
            Route::post('/allocations', 'EducationAllocationsController@edit')->name('.edit');
            // 登録
            Route::post('/allocations/store', 'EducationAllocationsController@store')->name('.store');
            // ステータスとstep_idの一意性チェック
            Route::post('/allocations/canMultipleAllocations', 'EducationAllocationsController@canMultipleAllocations')->name('.index');
        });

        // 一括割振(教育)
        Route::group(['prefix' => 'education', 'as' => 'multiple_allocations'], function () {
            // 初期表示
            Route::post('/multiple_allocations', 'EducationMultipleAllocationsController@getInitData')->name('.get_init_data');
            // 確認
            Route::post('/multiple_allocations/confirm', 'EducationMultipleAllocationsController@confirm')->name('.confirm');
            // 登録
            Route::post('/multiple_allocations/store', 'EducationMultipleAllocationsController@store')->name('.store');
        });

        // 割振
        Route::group(['prefix' => 'allocations', 'as' => 'allocations'], function () {
            // 一覧取得
            Route::post('index', 'AllocationsController@index')->name('.index');
            // 初期表示
            Route::post('', 'AllocationsController@edit')->name('.edit');
            // 登録
            Route::post('/store', 'AllocationsController@store')->name('.store');
            // 納品期限の変更
            Route::post('/changeDeliveryDeadline', 'AllocationsController@changeDeliveryDeadline')->name('.changeDeliveryDeadline');
            // ステータスとstep_idの一意性チェック
            Route::post('canMultipleAllocations', 'AllocationsController@canMultipleAllocations')->name('.index');
        });

        // 一括割振
        Route::group(['prefix' => 'multiple_allocations', 'as' => 'multiple_allocations'], function () {
            // 初期表示
            Route::post('', 'MultipleAllocationsController@getInitData')->name('.get_init_data');
            // 確認
            Route::post('/confirm', 'MultipleAllocationsController@confirm')->name('.confirm');
            // 登録
            Route::post('/store', 'MultipleAllocationsController@store')->name('.store');
        });

        // 実作業
        Route::group(['prefix' => 'works', 'as' => 'works'], function () {
            // 初期表示
            Route::post('', 'WorksController@index')->name('.index');
            // 納品期限の変更
            Route::post('/changeDeliveryDeadline', 'WorksController@changeDeliveryDeadline')->name('.changeDeliveryDeadline');
            // 作業一覧を取得
            Route::post('/getWorkList', 'WorksController@getWorkList')->name('.getWorkList');
        });

        // 承認
        Route::group(['prefix' => 'approvals', 'as' => 'approvals'], function () {
            // 初期表示
            Route::post('', 'ApprovalsController@edit')->name('.edit');
            // 検索
            Route::post('/index', 'ApprovalsController@index')->name('.index');
            // 登録
            Route::post('/store', 'ApprovalsController@store')->name('.store');
            // 再作業
            Route::post('/againTask', 'ApprovalsController@againTask')->name('.againTask');
            // 割振り
            Route::post('/allocate', 'ApprovalsController@allocate')->name('.allocate');
            // 納品期限の変更
            Route::post('/changeDeliveryDeadline', 'ApprovalsController@changeDeliveryDeadline')->name('.changeDeliveryDeadline');
        });

        // 納品
        Route::group(['prefix' => 'deliveries', 'as' => 'deliveries'], function () {
            // 検索
            Route::post('/index', 'DeliveriesController@index')->name('.index');
            // 登録
            Route::post('/store', 'DeliveriesController@store')->name('.store');
            // 再納品
            Route::post('/redelivery', 'DeliveriesController@redelivery')->name('.redelivery');
            // 納品予定データが入っているZipファイルを作成
            Route::post('/createTaskResultZipFile', 'DeliveriesController@createTaskResultZipFile')->name('.createTaskResultZipFile');
            // 納品データが入っているZipファイルを作成
            Route::post('/createZipFile', 'DeliveriesController@createZipFile')->name('.createZipFile');
            // 各依頼ごとの納品データの情報を取得
            Route::post('/deliveryInfo', 'DeliveriesController@deliveryInfo')->name('.deliveryInfo');
            // 再納品が可能かチェックする
            Route::post('/canRedelivery', 'DeliveriesController@canRedelivery')->name('.canRedelivery');
            // 納品期限の変更
            Route::post('/changeDeliveryDeadline', 'DeliveriesController@changeDeliveryDeadline')->name('.changeDeliveryDeadline');
            // 指定納品日の変更
            Route::post('/changeDeliveryAssignDate', 'DeliveriesController@changeDeliveryAssignDate')->name('.changeDeliveryAssignDate');
        });

        // 作業者
        Route::group(['prefix' => 'workers', 'as' => 'workers'], function () {
            // 詳細
            Route::post('/show', 'WorkersController@show')->name('.show');
            // 実績
            Route::post('/performance', 'WorkersController@performanceIndex')->name('.performance');
        });

        // ファイル取込
        Route::group(['prefix' => 'imported_files', 'as' => 'imported_files'], function () {
            // 一覧
            Route::post('', 'ImportedFilesController@index')->name('.index');
            // 最新ファイル一覧
            Route::post('/latest', 'ImportedFilesController@latest')->name('.latest');
            // 登録
            Route::post('/store', 'ImportedFilesController@store')->name('.store');
            // 削除
            Route::post('/delete', 'ImportedFilesController@delete')->name('.delete');
            // 取込対象業務一覧
            Route::get('/target_business_list', 'ImportedFilesController@targetBusinessList')->name('.target_business_list');
            // 一時保存
            Route::post('tmp_upload', 'ImportedFilesController@tmpUpload')->name('.tmp_upload');
            // 一時ファイル削除
            Route::post('tmp_file_delete', 'ImportedFilesController@tmpFileDelete')->name('.tmp_file_delete');
            // ファイル内容読込
            Route::post('read_file', 'ImportedFilesController@readFile')->name('.read_file');
            // ファイル取込設定
            Route::post('read_setting_order_file', 'ImportedFilesController@readSettingOrderFile')->name('.read_setting_order_file');
            // 設定した情報の取得
            Route::post('get_setting_information', 'ImportedFilesController@getSettingInformation')->name('.get_setting_information');
            // 案件内容取込
            Route::post('get_order_file_info', 'ImportedFilesController@getOrderFileInfo')->name('.get_order_file_info');
            // 登録
            Route::post('/order_store', 'ImportedFilesController@orderStore')->name('.order_store');
            // 案件明細の追加
            Route::post('/add_order_detail', 'ImportedFilesController@addOrderDetail')->name('.add_order_detail');
        });

        // 依頼補足情報
        Route::group(['prefix' => 'request_additional_infos', 'as' => 'request_additional_infos'], function () {
            // 一覧
            Route::post('', 'RequestAdditionalInfosController@index')->name('.index');
            // 登録
            Route::post('/store', 'RequestAdditionalInfosController@store')->name('.store');
            // 更新
            Route::post('/update', 'RequestAdditionalInfosController@update')->name('.update');
            // 削除
            Route::post('/delete', 'RequestAdditionalInfosController@delete')->name('.delete');
            // 添付ファイル削除
            Route::post('/delete_attachment', 'RequestAdditionalInfosController@deleteAttachment')->name('.delete_attachment');
        });

        // レポート
        Route::group(['prefix' => 'reports', 'as' => 'reports'], function () {
            // レポート出力
            Route::post('/output', 'ReportsController@output')->name('.output');
            // 初期表示
            Route::get('/getReportInfo', 'ReportsController@getReportInfo')->name('.getReportInfo');
        });

        // 案件
        Route::group(['prefix' => 'order', 'as' => 'order', 'namespace' => 'Order'], function () {
            // 案件
            Route::group(['prefix' => 'orders', 'as' => 'orders'], function () {
                // 一覧
                Route::post('index', 'OrdersController@index')->name('.index');
                // 一括更新
                Route::post('bulk_update', 'OrdersController@bulkUpdate')->name('.bulk_update');
                // DL機能
                Route::post('create_each_order_file', 'OrdersController@createEachOrderFile')->name('.create_each_order_file');
                // 設定
                Route::post('edit', 'OrdersController@edit')->name('.edit');
                // 更新
                Route::post('/edit/update', 'OrdersController@update')->name('.update');
                // 項目設定
                Route::get('{order_id}/item/edit', 'OrdersController@editItems')->name('.item_edit');
                // 項目更新
                Route::post('{order_id}/item/update', 'OrdersController@updateItems')->name('.item_update');
                // 既存の案件管理者と共有者取得
                Route::post('/order_candidates', 'OrdersController@orderCandidates')->name('.order_candidates');
                // 案件管理候補者を取得
                Route::post('/admin_candidate_users', 'OrdersController@adminCandidateUsers')->name('.admin_candidate_users');
                // 共有候補者を取得
                Route::post('/sharer_candidate_users', 'OrdersController@sharerCandidateUsers')->name('.sharer_candidate_users');
                // 管理者を追加
                Route::post('/add_administrators', 'OrdersController@addAdministrators')->name('.add_administrators');
                // 共有者を追加
                Route::post('/add_sharers', 'OrdersController@addSharers')->name('.add_sharers');
                // 管理者を削除
                Route::post('/delete_administrators', 'OrdersController@deleteAdministrators')->name('.delete_administrators');
                // 共有者を削除
                Route::post('/delete_sharers', 'OrdersController@deleteSharers')->name('.delete_sharers');
            });
            // 案件明細
            Route::group(['prefix' => 'order_details', 'as' => 'order_details'], function () {
                // 一覧
                Route::post('index', 'OrderDetailsController@index')->name('.index');
                // 一括更新
                Route::post('bulk_update', 'OrderDetailsController@bulkUpdate')->name('.bulk_update');
                // 案件ファイルのDL
                Route::post('create_order_file', 'OrderDetailsController@createOrderFile')->name('.create_order_file');
                // 詳細
                Route::post('/create', 'OrderDetailsController@show')->name('.create');
                // 保存
                Route::post('/save', 'OrderDetailsController@save')->name('.save');
                // 依頼作成
                Route::post('/create_requests', 'OrderDetailsController@createRequests')->name('.create_requests');
                // 依頼一括作成
                Route::post('/bulk_create_requests', 'OrderDetailsController@bulkCreateRequests')->name('.bulk_create_requests');
                // 依頼一括作成に必要なデータ取得
                Route::post('/get_create_request_data', 'OrderDetailsController@getCreateRequestData')->name('.get_order_detail_columns');
                // 依頼取得
                Route::post('/search_requests', 'OrderDetailsController@searchRequests')->name('.search_requests');
                // 業務情報取得
                Route::post('/search_businesses', 'OrderDetailsController@searchBusinesses')->name('.search_businesses');
                // 表示形式を設定
                Route::post('/setting_display_format', 'OrderDetailsController@settingDisplayFormat')->name('.setting_display_format');
                // 案件明細の関連メール送信先メールアドレス生成
                Route::post('/alias_mail_address', 'OrderDetailsController@aliasMailAddress')->name('.alias_mail_address');
                // 関連メール削除
                Route::post('/delete_order_related_mails', 'OrderDetailsController@deleteOrderRelatedMail')->name('.delete_order_related_mails');
                // 関連メール取得
                Route::post('/get_related_mail_list', 'OrderDetailsController@getRelatedMailList')->name('.get_related_mail_list');
                // メール作成
                Route::post('/create_send_mail', 'OrderDetailsController@createSendMail')->name('.create_send_mail');
            });
            // 案件補足情報
            Route::group(['prefix' => 'additional_infos', 'as' => 'additional_infos'], function () {
                // 一覧
                Route::get('index', 'OrderAdditionalInfosController@index')->name('.index');
                // 登録
                Route::post('store', 'OrderAdditionalInfosController@store')->name('.store');
                // 更新
                Route::post('update', 'OrderAdditionalInfosController@update')->name('.update');
                // 削除
                Route::post('delete', 'OrderAdditionalInfosController@delete')->name('.delete');
                // 添付ファイル削除
                Route::post('delete_attachment', 'OrderAdditionalInfosController@deleteAttachment')->name('.delete_attachment');
            });
        });

        /**
        *   業務
        */
        Route::group(['prefix' => 'biz', 'as' => 'biz', 'namespace' => 'Biz'], function () {
            // WF概算修正業務
            Route::group([
                'prefix' => 'wf_gaisan_syusei',
                'as' => '.wf_gaisan_syusei',
                'namespace' => 'WfGaisanSyusei'
            ], function () {
                // メール仕訳け
                Route::group([
                    'prefix' => 'assort_mail',
                    'as' => '.assort_mail',
                ], function () {
                    // 仕訳け画面
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'AssortMailController@create'])->name('.create');
                    // データ登録
                    Route::post('store', ['uses' => 'AssortMailController@store'])->name('.store');
                });
                // メール情報入力
                Route::group([
                    'prefix' => 'input_mail_info',
                    'as' => '.input_mail_info',
                ], function () {
                    // 入力画面
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'InputMailInfoController@create'])->name('.create');
                    // データ登録
                    Route::post('store', ['uses' => 'InputMailInfoController@store'])->name('.store');
                });
                // SAS情報入力
                Route::group([
                    'prefix' => 'input_sas_info',
                    'as' => '.input_sas_info',
                ], function () {
                    // 入力画面
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'InputSasInfoController@create'])->name('.create');
                    // データ登録
                    Route::post('store', ['uses' => 'InputSasInfoController@store'])->name('.store');
                });
                // パワポ特定
                Route::group([
                    'prefix' => 'identify_ppt',
                    'as' => '.identify_ppt',
                ], function () {
                    // 入力画面
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'IdentifyPptController@create'])->name('.create');
                    // データ登録
                    Route::post('store', ['uses' => 'IdentifyPptController@store'])->name('.store');
                });
                // パワポ情報入力
                Route::group([
                    'prefix' => 'input_ppt_info',
                    'as' => '.input_ppt_info',
                ], function () {
                    // 入力画面
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'InputPptInfoController@create'])->name('.create');
                    // データ登録
                    Route::post('store', ['uses' => 'InputPptInfoController@store'])->name('.store');
                });
                // パワポ情報入力
                Route::group([
                    'prefix' => 'final_judge',
                    'as' => '.final_judge',
                ], function () {
                    // 入力画面
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'FinalJudgeController@create'])->name('.create');
                    // データ登録
                    Route::post('store', ['uses' => 'FinalJudgeController@store'])->name('.store');
                });
            });

            // Abbeyチェック
            Route::group([
                'prefix' => 'abbey_check',
                'as' => '.abbey_check',
                'namespace' => 'AbbeyCheck'
            ], function () {

                // Abbeyチェック
                Route::group([
                    'prefix' => 'abbey_check',
                    'as' => '.abbey_check',
                ], function () {
                    // Abbeyチェック画面
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'AbbeyCheckController@create'])->name('.create');
                    // データ登録
                    Route::post('store', ['uses' => 'AbbeyCheckController@store'])->name('.store');
                    // データ登録
                    Route::post('convert', ['uses' => 'AbbeyCheckController@convert'])->name('.convert');

                    Route::post('download_file', ['uses' => 'AbbeyCheckController@downloadFromS3'])->name('.downloadFile');
                });
                // Abbey検索
                Route::group([
                    'prefix' => 'abbey_search',
                    'as' => '.abbey_search',
                ], function () {
                    // Abbey検索画面
                    Route::post('index', ['uses' => 'AbbeySearchController@index'])->name('.index');
                    // データ検索
                    // Route::post('store', ['uses' => 'AbbeySearchController@store'])->name('.store');
                });
            });

            // 共通
            Route::group(['prefix' => 'base', 'as' => 'base'], function () {
                // 関連情報取得
                Route::post('/getAppendices', 'BaseController@getAppendices')->name('.getAppendices');
            });
        });

        Route::group(['prefix' => 'biz', 'as' => 'biz', 'namespace' => 'Biz'], function () {
            // 開発業務
            Route::group([
                'prefix' => 'development',
                'as' => '.development',
                'namespace' => 'Development'
            ], function () {
                // 経費承認
                Route::group([
                    'prefix' => 'improvement',
                    'as' => '.improvement',
                ], function () {
                    // 承認画面
                    Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'ImprovementController@create'])->name('.create');
                    // データ登録
                    Route::post('store', ['uses' => 'ImprovementController@store'])->name('.store');
                });
            });
        });
    });


    /* -------------------- web ------------------------- */

    // 共通
    Route::group(['prefix' => 'utilities', 'as' => 'utilities'], function () {
        // ファイルダウンロード
        Route::get('download_file', 'UtilitiesController@downloadFile')->name('.downloadFile');
    });

    // 管理
    Route::group(['prefix' => 'management', 'as' => 'management'], function () {
        // 業務
        Route::group(['prefix' => 'businesses', 'as' => 'businesses'], function () {
            // 一覧
            Route::get('', 'Management\BusinessesController@index')->name('.index');
            // 詳細
            Route::get('/{business_id}', 'Management\BusinessesController@show')->name('.show');
        });
        // 業務状況
        Route::group(['prefix' => 'business_states', 'as' => 'business_states'], function () {
            // 一覧
            Route::get('', 'Management\BusinessStatesController@index')->name('.index');
        });
        // レポート
        Route::group(['prefix' => 'reports', 'as' => 'reports'], function () {
            // 一覧
            Route::get('', 'Management\ReportsController@index')->name('.index');
        });
        // 依頼作業
        Route::group(['prefix' => 'request_works', 'as' => 'request_works'], function () {
            // 一覧
            Route::get('', 'Management\RequestWorksController@index')->name('.index');
            // 詳細
            Route::get('/{request_work_id}', 'Management\RequestWorksController@show')->name('.show');
        });
        // 依頼
        Route::group(['prefix' => 'requests', 'as' => 'requests'], function () {
            // 一覧
            Route::get('', 'Management\RequestsController@index')->name('.index');
            // 詳細
            Route::get('/{request_id}', 'Management\RequestsController@show')->name('.show');
        });
        // 作業
        Route::group(['prefix' => 'works', 'as' => 'works'], function () {
            // 一覧
            Route::get('', 'Management\WorksController@index')->name('.index');
        });
        // 作業者
        Route::group(['prefix' => 'workers', 'as' => 'workers'], function () {
            // 詳細
            Route::get('/{user_id}', 'Management\WorkersController@detail')->name('.detail');
        });
        // ユーザー情報
        Route::group(['prefix' => 'user', 'as' => 'user'], function () {
            // 詳細
            Route::get('', 'Management\UsersController@index')->name('.index');
        });
    });

    // 案件
    Route::group(['prefix' => 'order', 'as' => 'order', 'namespace' => 'Order'], function () {
        // 案件
        Route::group(['prefix' => 'orders', 'as' => 'orders'], function () {
            // 一覧
            Route::get('', 'OrdersController@index')->name('.index');
            // 設定
            Route::get('{order_id}/edit', 'OrdersController@edit')->name('.edit');
            // 項目設定
            Route::get('{order_id}/item/edit', 'OrdersController@itemEdit')->name('.itemEdit');
        });
        // 案件明細
        Route::group(['prefix' => 'order_details', 'as' => 'order_details'], function () {
            // 一覧
            Route::get('', 'OrderDetailsController@index')->name('.index');
            // 詳細
            Route::get('/create', 'OrderDetailsController@show')->name('.show');
            // 詳細
            Route::get('/{order_detail_id}/edit', 'OrderDetailsController@show')->name('.show');
        });
    });

    // マスタ管理
    Route::group(['prefix' => 'master', 'as' => 'master'], function () {
        // 作業マスタ
        Route::group(['prefix' => 'steps', 'as' => 'steps'], function () {
            // 一覧
            Route::get('', 'Master\StepsController@index')->name('.index');
        });
        // 業務マスタ
        Route::group(['prefix' => 'businesses', 'as' => 'businesses'], function () {
            // 一覧
            Route::get('', 'Master\BusinessesController@index')->name('.index');
            // 詳細
            Route::get('/{business_id}', 'Master\BusinessesController@show')->name('.show');
        });
        // ユーザーマスタ
        Route::group(['prefix' => 'users', 'as' => 'businesses'], function () {
            // 一覧
            Route::get('', 'Master\UsersController@index')->name('.index');
        });
    });

    // タスク
    Route::group(['prefix' => 'tasks', 'as' => 'tasks'], function () {
        // 一覧
        Route::get('', 'TasksController@index')->name('.index');
    });

    // 検証
    Route::group(['prefix' => 'verification', 'as' => 'verification'], function () {
        // 検証
        Route::get('/{request_work_id}/{task_id}/edit', 'VerificationController@edit')->name('.edit');
    });

    // 割振
    Route::group(['prefix' => 'allocations', 'as' => 'allocations'], function () {
        // 一覧
        Route::get('', 'AllocationsController@index')->name('.index');
        // 個別
        Route::get('/{request_work_id}/edit', 'AllocationsController@edit')->name('.edit');
        // 一括
        Route::get('/create', 'AllocationsController@create')->name('.create');
        // 登録
        Route::post('store', ['uses' => 'AllocationsController@store'])->name('.store');
    });
    // 一括割振
    Route::group(['prefix' => 'multiple_allocations', 'as' => 'multiple_allocations'], function () {
        // 設定
        Route::post('/create', 'MultipleAllocationsController@create')->name('.create');
        // 確認
        Route::post('/confirm', 'MultipleAllocationsController@confirm')->name('.confirm');
    });

    // 承認
    Route::group(['prefix' => 'approvals', 'as' => 'approvals'], function () {
        // 一覧
        Route::get('', 'ApprovalsController@index')->name('.index');
        // 承認
        Route::get('/{request_work_id}/edit', 'ApprovalsController@edit')->name('.edit');
        // ファイルプレビュー
        Route::post('/filePreview', 'ApprovalsController@filePreview')->name('.filePreview');
    });
    // 納品
    Route::group(['prefix' => 'deliveries', 'as' => 'deliveries'], function () {
        // 一覧
        Route::get('', 'DeliveriesController@index')->name('.index');
        // 詳細
        Route::get('/{request_work_id}', 'DeliveriesController@detail')->name('.detail');
    });

    // ファイル取込
    Route::group(['prefix' => 'imported_files', 'as' => 'imported_files'], function () {
        // 一覧
        Route::get('', 'ImportedFilesController@index')->name('.index');
        // ファイル取込設定
        Route::post('/order_setting', 'ImportedFilesController@orderSetting')->name('.orderSetting');
        // 読込内容確認
        Route::get('/confirm', 'ImportedFilesController@confirm')->name('.confirm');
        // 案件ファイル読込内容確認
        Route::post('/order_confirm', 'ImportedFilesController@orderConfirm')->name('.orderConfirm');
    });



    // 教育
    Route::group(['prefix' => 'education', 'as' => 'education'], function () {
        // 割振
        Route::group(['prefix' => 'allocations', 'as' => 'allocations'], function () {
            // 一覧
            Route::get('', 'EducationAllocationsController@index')->name('.index');
            // 個別
            Route::get('/{request_work_id}/edit', 'EducationAllocationsController@edit')->name('.edit');
            // 一括
            Route::post('/create', 'EducationAllocationsController@create')->name('.create');
            // 一括（確認）
            Route::post('/confirm', 'EducationAllocationsController@confirm')->name('.confirm');
        });
    });

    // [業務共通]
    Route::group(['prefix' => 'biz', 'as' => 'biz', 'namespace' => 'Biz'], function () {
        // [業務共通]各種作業画面
        Route::get(
            '/b{business_id}/s{step_id}/{request_work_id}/{task_id}/create',
            'BaseController@create'
        );
    });

    // 業務
    Route::group(['prefix' => 'biz', 'as' => 'biz', 'namespace' => 'Biz'], function () {
        // WF概算修正業務
        Route::group([
            'prefix' => 'wf_gaisan_syusei',
            'as' => '.wf_gaisan_syusei',
            'namespace' => 'WfGaisanSyusei'
        ], function () {
            // スプレッドシートAPI利用承認取得
            Route::get('/get_google_o_auth_credentials', 'WfGaisanSyuseiController@getGoogleOAuthCredentials');
            // スプレッドシートAPI利用承認取得コールバック
            Route::get('/oauth2_callback', 'WfGaisanSyuseiController@oauth2Callback');
            // スプレッドシート取得
            Route::post('/get_master_data_on_spreadsheet', 'WfGaisanSyuseiController@getMasterDataOnSpreadsheet');

            // メール仕訳け作業
            Route::group([
                'prefix' => 'assort_mail',
                'as' => '.assort_mail',

            ], function () {
                // メール仕訳け画面
                Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'AssortMailController@create'])->name('.create');
                // メール仕訳けデータ登録
                Route::post('store', ['uses' => 'AssortMailController@store'])->name('.store');
            });
            // メール情報入力作業
            Route::group([
                'prefix' => 'input_mail_info',
                'as' => '.input_mail_info',

            ], function () {
                // メール情報入力画面
                Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'InputMailInfoController@create'])->name('.create');
                // メール情報入力登録
                Route::post('store', ['uses' => 'InputMailInfoController@store'])->name('.store');
            });
            // SAS情報入力作業
            Route::group([
                'prefix' => 'input_sas_info',
                'as' => '.input_sas_info',

            ], function () {
                // SAS情報入力画面
                Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'InputSasInfoController@create'])->name('.create');
                // SAS情報入力登録
                Route::post('store', ['uses' => 'InputSasInfoController@store'])->name('.store');
            });
            // パワポ特定作業
            Route::group([
                'prefix' => 'identify_ppt',
                'as' => '.identify_ppt',

            ], function () {
                // パワポ情報入力画面
                Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'IdentifyPptController@create'])->name('.create');
                // パワポ情報入力登録
                Route::post('store', ['uses' => 'IdentifyPptController@store'])->name('.store');
                // 依頼メール添付ファイルダウンロード
                Route::get('/download_attachment_file', 'IdentifyPptController@downloadAttachmentFile')->name('.download_attachment_file');
            });
            // パワポ情報入力作業
            Route::group([
                'prefix' => 'input_ppt_info',
                'as' => '.input_ppt_info',

            ], function () {
                // パワポ情報入力画面
                Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'InputPptInfoController@create'])->name('.create');
                // パワポ情報入力登録
                Route::post('store', ['uses' => 'InputPptInfoController@store'])->name('.store');
                // 依頼メール添付ファイルダウンロード
                Route::get('/download_attachment_file', 'InputPptInfoController@downloadAttachmentFile')->name('.download_attachment_file');
            });
            // 最終判定作業
            Route::group([
                'prefix' => 'final_judge',
                'as' => '.final_judge',

            ], function () {
                // 最終判定画面
                Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'FinalJudgeController@create'])->name('.create');
                // 最終判定結果登録
                Route::post('store', ['uses' => 'FinalJudgeController@store'])->name('.store');
            });
        });

        // Abbeyチェック
        Route::group([
            'prefix' => 'abbey_check',
            'as' => '.abbey_check',
            'namespace' => 'AbbeyCheck'
        ], function () {

            // Abbeyチェック
            Route::group([
                'prefix' => 'abbey_check',
                'as' => '.abbey_check',
            ], function () {
                // Abbeyチェック画面
                Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'AbbeyCheckController@create'])->name('.create');
                // データ登録
                Route::post('store', ['uses' => 'AbbeyCheckController@store'])->name('.store');
                Route::post('convert', ['uses' => 'AbbeyCheckController@convert'])->name('.convert');
            });
            // Abbey検索
            Route::group([
                'prefix' => 'abbey_search',
                'as' => '.abbey_search',
            ], function () {
                // Abbey検索画面
                Route::get('create', ['uses' => 'AbbeySearchController@create'])->name('.create');
            });
        });

        // 開発業務
        Route::group([
            'prefix' => 'development',
            'as' => '.development',
            'namespace' => 'Development'
        ], function () {
            // 経費承認
            Route::group([
                'prefix' => 'improvement',
                'as' => '.improvement',
            ], function () {
                // 経費承認画面
                Route::get('/{request_work_id}/{task_id}/create', ['uses' => 'ImprovementController@create'])->name('.create');
                // 経費承認結果登録
                Route::post('store', ['uses' => 'ImprovementController@store'])->name('.store');
            });
        });
    });
});


/* -------------------- users と guest_clients 共通 ------------------------- */

Route::group(['middleware' => 'auth:web,guest_client'], function () {

    /* -------------------- web ------------------------- */
    Route::group(['prefix' => 'client', 'as' => 'client', 'namespace' => 'Client'], function () {
        // 依頼
        Route::group(['prefix' => 'requests', 'as' => 'requests'], function () {
            // 詳細
            Route::get('/{request_id}', 'RequestsController@show')->name('.show');
        });
    });

    /* -------------------- api ------------------------- */
    Route::group(['prefix' => 'api', 'as' => 'api', 'namespace' => 'Api'], function () {
        Route::group(['prefix' => 'utilities', 'as' => 'utilities'], function () {
            // 作業依頼内容を取得
            Route::get('/getWorkRequestInfo', 'UtilitiesController@getWorkRequestInfo')->name('.workRequestInfo');
        });

        Route::group(['prefix' => 'client', 'as' => 'client', 'namespace' => 'Client'], function () {
            // 依頼
            Route::group(['prefix' => 'requests', 'as' => 'requests'], function () {
                // 付加情報
                Route::post('/appendices', 'RequestsController@getAppendices')->name('.appendices');
            });
        });
        Route::group(['prefix' => 'requests', 'as' => 'requests'], function () {
            // 詳細
            Route::post('/show', 'RequestsController@show')->name('.show');
        });
    });
});

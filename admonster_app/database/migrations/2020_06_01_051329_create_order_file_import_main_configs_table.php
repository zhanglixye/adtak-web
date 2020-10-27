<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderFileImportMainConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_file_import_main_configs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->string('sheet_name', 256)->comment('シート名');
            $table->unsignedInteger('header_row_no')->comment('ヘッダー行番号');
            $table->unsignedInteger('data_start_row_no')->comment('取込データ開始行番号');
            $table->string('start_column', 256)->comment('開始カラム名');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE order_file_import_main_configs COMMENT '案件ファイル取込メイン設定'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_file_import_main_configs');
    }
}

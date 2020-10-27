<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileImportMainConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_import_main_configs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('step_id')->comment('作業ID');
            $table->foreign('step_id')->references('id')->on('steps')->onDelete('cascade');
            $table->string('sheet_name', 256)->nullable()->comment('シート名');
            $table->integer('header_row_no')->nullable()->comment('ヘッダー行番号');
            $table->integer('data_start_row_no')->comment('取込データ開始行番号');
            $table->string('subject_delimiter', 256)->nullable()->comment('件名区切り文字');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE file_import_main_configs COMMENT 'ファイル取込メイン設定'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_import_main_configs');
    }
}

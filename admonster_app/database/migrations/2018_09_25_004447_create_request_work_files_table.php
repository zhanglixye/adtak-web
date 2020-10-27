<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestWorkFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_work_files', function (Blueprint $table) {
            $table->unsignedBigInteger('request_work_id')->comment('依頼作業ID');
            $table->foreign('request_work_id')->references('id')->on('request_works')->onDelete('cascade');
            $table->unsignedBigInteger('request_file_id')->comment('依頼ファイルID');
            $table->foreign('request_file_id')->references('id')->on('request_files')->onDelete('cascade');
            $table->primary(['request_work_id', 'request_file_id'], 'request_work_file_id_primary');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE request_work_files COMMENT '依頼作業のファイル'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_work_files');
    }
}

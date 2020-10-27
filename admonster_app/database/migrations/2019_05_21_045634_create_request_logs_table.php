<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('request_id')->comment('依頼ID');
            $table->foreign('request_id')->references('id')->on('requests')->onDelete('cascade');
            $table->smallInteger('type')->comment('種別(conifgファイルで管理)');
            $table->bigInteger('request_work_id')->nullable()->comment('依頼作業ID');
            $table->bigInteger('task_id')->nullable()->comment('タスクID');
            $table->bigInteger('request_additional_info_id')->nullable()->comment('依頼補足情報ID');
            $table->string('comment', 256)->nullable()->comment('コメント');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE request_logs COMMENT '依頼に関するログ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_logs');
    }
}

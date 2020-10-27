<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreateRequestWorkConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('create_request_work_configs', function (Blueprint $table) {
            $table->unsignedBigInteger('step_id')->comment('作業ID');
            $table->foreign('step_id')->references('id')->on('steps')->onDelete('cascade');
            $table->primary(['step_id']);
            $table->string('split_items', 256)->nullable()->comment('分割項目');
            $table->string('code', 256)->nullable()->comment('取込みデータ番号');
            $table->string('name', 256)->nullable()->comment('依頼作業名');
            $table->string('client_name', 256)->nullable()->comment('依頼主名');
            $table->string('from', 256)->nullable()->comment('掲載期間開始');
            $table->string('to', 256)->nullable()->comment('掲載期間終了');
            $table->string('deadline', 256)->nullable()->comment('期限');
            $table->string('remarks', 256)->nullable()->comment('備考');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE create_request_work_configs COMMENT '依頼作業作成設定'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('create_request_work_configs');
    }
}

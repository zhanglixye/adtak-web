<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkTimeSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_time_summaries', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->dateTime('actual_date')->comment('イベントの実績日(yyyy-mm-dd)');
            $table->tinyInteger('user_type')->comment('ユーザー種別（1:通常ユーザ, 2:クライアント）');
            $table->unsignedBigInteger('user_id')->comment('各ユーザーのid(参照テーブル:users.id、guest_clients.id)');
            $table->tinyInteger('process_type')->comment('1:取込、2:割振、3:タスク、4:承認、5:納品、6:管理、7:マスタ設定、8:クライアント確認、9:他');
            $table->unsignedBigInteger('business_id')->nullable()->comment('業務ID');
            $table->unsignedBigInteger('step_id')->nullable()->comment('作業ID');
            $table->unsignedDecimal('work_time', 6, 2)->comment('工数時間（単位:秒）');
            $table->boolean('education_flg')->default(0)->comment('教育(0:false, 1:true)');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });
        // テーブルコメント定義
        DB::statement("ALTER TABLE work_time_summaries COMMENT 'すべてのイベントの作業時間のサマリーテーブル'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_time_summaries');
    }
}

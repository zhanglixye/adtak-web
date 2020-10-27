<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskWorkTimeSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_work_time_summaries', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->dateTime('actual_date')->comment('イベントの実績日(yyyy-mm-dd)');
            $table->unsignedBigInteger('task_id')->nullable()->comment('タスクID');
            $table->unsignedDecimal('work_time', 6, 2)->comment('工数時間（単位:秒）');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });
        // テーブルコメント定義
        DB::statement("ALTER TABLE task_work_time_summaries COMMENT 'タスク工程の作業時間のサマリーテーブル'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_work_time_summaries');
    }
}

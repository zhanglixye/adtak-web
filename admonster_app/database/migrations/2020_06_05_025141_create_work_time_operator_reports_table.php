<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkTimeOperatorReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_time_operator_reports', function (Blueprint $table) {
            $table->date('actual_date')->comment('イベントの実績日(yyyy-mm-dd)');
            $table->unsignedBigInteger('user_id')->comment('ユーザーID');
            $table->unsignedBigInteger('business_id')->nullable()->comment('業務ID');
            $table->unsignedBigInteger('step_id')->nullable()->comment('作業ID');
            $table->tinyInteger('process_type')->comment('1:取込、2:割振、3:タスク、4:承認、5:納品、6:管理、7:マスタ設定、8:クライアント確認、9:検証、99:他');
            $table->unsignedSmallInteger('work_count')->nullable()->comment('作業件数');
            $table->unsignedSmallInteger('ok_count')->nullable()->comment('承認OK件数');
            $table->unsignedSmallInteger('ng_count')->nullable()->comment('承認NG件数');
            $table->unsignedDecimal('system_work_time', 6, 2)->comment('システム工数(分で保持)');
            $table->unsignedDecimal('manual_work_time', 6, 2)->comment('手動工数(分で保持)');
            $table->boolean('education_flg')->default(0)->comment('教育(0:false, 1:true)');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });
        // テーブルコメント定義
        DB::statement("ALTER TABLE work_time_operator_reports COMMENT '人別工数レポートテーブル'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_time_operator_reports');
    }
}

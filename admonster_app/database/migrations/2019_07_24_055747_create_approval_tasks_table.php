<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApprovalTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_tasks', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('approval_id')->comment('承認ID');
            $table->foreign('approval_id')->references('id')->on('approvals')->onDelete('cascade');
            $table->unsignedBigInteger('task_id')->comment('タスクID');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->tinyInteger('approval_result')->nullable()->comment('承認結果(0:OK、1:NG)');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE approval_tasks COMMENT '承認タスク'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_tasks');
    }
}

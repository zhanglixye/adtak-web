<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAllocationsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('allocations_users', function (Blueprint $table) {
            $table->unsignedBigInteger('step_id')->comment('作業ID');
            $table->foreign('step_id')->references('id')->on('steps')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->comment('ユーザーID');
            $table->foreign('user_id')->references('id')->on('steps')->onDelete('cascade');
            $table->primary(['step_id', 'user_id']);

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE allocations_users COMMENT '割振り担当者'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('allocations_users');
    }
}

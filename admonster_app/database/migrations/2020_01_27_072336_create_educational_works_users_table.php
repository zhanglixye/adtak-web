<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEducationalWorksUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('educational_works_users', function (Blueprint $table) {
            $table->unsignedBigInteger('step_id')->comment('作業ID');
            $table->foreign('step_id')->references('id')->on('steps')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->comment('ユーザID');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->primary(['step_id', 'user_id']);

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE educational_works_users COMMENT '作業担当候補者（教育）'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('educational_works_users');
    }
}

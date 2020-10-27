<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->tinyInteger('user_type')->comment('ユーザー種別（1:通常ユーザ, 2:クライアント）');
            $table->unsignedBigInteger('user_id')->comment('各ユーザーのid(参照テーブル:users.id、guest_clients.id)');
            $table->string('ip', 16)->comment('ユーザIPアドレス');
            $table->string('request_url', 256)->comment('リクエストのURL');
            $table->longText('post_data')->nullable()->comment('ポスト内容');
            $table->dateTime('occurred_at')->comment('このイベントの発生時間');
            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });
        // テーブルコメント定義
        DB::statement("ALTER TABLE event_logs COMMENT 'イベントログ'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_logs');
    }
}

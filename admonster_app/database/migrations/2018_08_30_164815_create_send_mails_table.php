<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSendMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('send_mails', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('request_work_id')->nullable()->comment('依頼作業ID');
            $table->string('references', 256)->nullable()->comment('References');
            $table->string('in_reply_to', 256)->nullable()->comment('In-Reply-To');
            $table->string('reply_to', 256)->nullable()->comment('Reply-To');
            $table->longText('from')->nullable()->comment('From');
            $table->longText('to')->nullable()->comment('To');
            $table->longText('cc')->nullable()->comment('Cc');
            $table->longText('bcc')->nullable()->comment('Bcc');
            $table->longText('subject')->nullable()->comment('件名');
            $table->longText('body')->nullable()->comment('本文');
            $table->dateTime('sended_at')->nullable()->comment('送信日時');

            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');
            
            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE send_mails COMMENT '送信メール'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('send_mails');
    }
}

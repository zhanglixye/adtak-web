<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_mails', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('step_id')->nullable()->comment('作業ID');
            $table->tinyInteger('create_status')->comment('依頼作成ステータス(-1:依頼作成失敗, 0:未依頼作成, 1:依頼作成完了, 9:依頼作成対象外, 99:依頼作成中)');
            $table->longText('message')->nullable()->comment('Message');
            $table->string('message_id', 256)->nullable()->comment('Message-ID');
            $table->string('references', 256)->nullable()->comment('References');
            $table->string('in_reply_to', 256)->nullable()->comment('In-Reply-To');
            $table->string('reply_to', 256)->comment('Reply-To');
            $table->longText('from')->nullable()->comment('From');
            $table->longText('to')->nullable()->comment('To');
            $table->longText('cc')->nullable()->comment('Cc');
            $table->longText('bcc')->nullable()->comment('Bcc');
            $table->longText('subject')->nullable()->comment('件名');
            $table->longText('body')->nullable()->comment('本文');
            $table->dateTime('recieved_at')->nullable()->comment('受信日時');
            
            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');
            
            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE request_mails COMMENT '依頼メール'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_mails');
    }
}

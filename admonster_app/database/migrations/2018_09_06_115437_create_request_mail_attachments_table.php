<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestMailAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_mail_attachments', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('request_mail_id')->nullable()->comment('依頼メールID');
            $table->foreign('request_mail_id')->references('id')->on('request_mails')->onDelete('cascade');
            $table->string('name', 256)->nullable()->comment('添付ファイル名');
            $table->string('file_path', 256)->nullable()->comment('添付ファイルパス');
            
            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE request_mail_attachments COMMENT '依頼メールの添付'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_mail_attachments');
    }
}

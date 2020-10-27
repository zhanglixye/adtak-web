<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestMailAttachmentExtraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_mail_attachment_extra', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mail_attachment_id')->comment('添付ID');
            $table->foreign('mail_attachment_id')->references('id')->on('request_mail_attachments')->onDelete('cascade');
            $table->string('name', 256)->nullable()->comment('ファイル名');
            $table->string('file_path', 256)->nullable()->comment('ファイルパス');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE request_mail_attachment_extra COMMENT '依頼メールの添付（追加）'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_mail_attachment_extra');
    }
}

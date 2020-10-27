<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyFileAttachmentCommentCommonMailSettingsTable extends Migration
{
    /**
     * Run the migrations
     *
     * @return void
     */
    public function up()
    {

        Schema::table('common_mail_settings', function (Blueprint $table) {
            $table->integer('file_attachment')->default(3)->comment('「ファイル添付」機能設定,3つステータスの組み合わせ値(32:required+16:empty_alert+4:default+2:enable+1:display)')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('common_mail_settings', function (Blueprint $table) {
            $table->integer('file_attachment')->default(3)->comment('「ファイル添付」機能設定,3つステータスの組み合わせ値(4:default+2:enable+1:display)')->change();
        });
    }
}

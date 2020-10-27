<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestWorkRelatedMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_work_related_mails', function (Blueprint $table) {
            $table->unsignedBigInteger('request_work_id')->comment('依頼作業ID');
            $table->foreign('request_work_id')->references('id')->on('request_works')->onDelete('cascade');
            $table->unsignedBigInteger('request_mail_id')->comment('依頼メールID');
            $table->foreign('request_mail_id')->references('id')->on('request_mails')->onDelete('cascade');
            $table->boolean('is_open_to_client')->default(0)->comment('クライアントへの公開(0:false, 1:true)');
            $table->primary(['request_work_id', 'request_mail_id'], 'request_mail_id_primary');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_work_related_mails');
    }
}

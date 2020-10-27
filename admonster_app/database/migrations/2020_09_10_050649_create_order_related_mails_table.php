<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderRelatedMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_related_mails', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('order_id')->comment('案件ID');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->unsignedBigInteger('order_detail_id')->nullable()->comment('案件明細ID');
            $table->foreign('order_detail_id')->references('id')->on('order_details')->onDelete('cascade');
            $table->unsignedBigInteger('request_mail_id')->nullable()->unique()->comment('依頼メールID');
            $table->foreign('request_mail_id')->references('id')->on('request_mails')->onDelete('cascade');
            $table->unsignedBigInteger('send_mail_id')->nullable()->unique()->comment('送信メールID');
            $table->foreign('send_mail_id')->references('id')->on('send_mails')->onDelete('cascade');
            $table->boolean('is_open_to_client')->default(0)->comment('クライアントへの公開(0:false, 1:true)');
            $table->longText('from')->nullable()->comment('送信元');
            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE order_related_mails COMMENT '案件関連のメール'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_related_mails');
    }
}

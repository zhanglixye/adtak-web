<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportOrderRelatedMailAliasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_order_related_mail_aliases', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->comment('案件ID');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->unsignedBigInteger('order_detail_id')->nullable()->comment('案件明細ID');
            $table->foreign('order_detail_id')->references('id')->on('order_details')->onDelete('cascade');
            $table->string('alias', 256)->comment('エイリアス');
            $table->boolean('is_open_to_client')->default(0)->comment('クライアントへの公開(0:false, 1:true)');
            $table->longText('from')->nullable()->comment('送信元');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_order_related_mail_aliases');
    }
}

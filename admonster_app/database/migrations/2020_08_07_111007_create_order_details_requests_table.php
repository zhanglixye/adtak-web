<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderDetailsRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details_requests', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('order_detail_id')->comment('案件明細ID');
            $table->foreign('order_detail_id')->references('id')->on('order_details')->onDelete('cascade');
            $table->unsignedBigInteger('request_id')->comment('依頼ID');
            $table->foreign('request_id')->references('id')->on('requests')->onDelete('cascade');
            $table->unique(['order_detail_id', 'request_id']);

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE order_details_requests COMMENT '案件明細と依頼の中間テーブル'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_details_requests');
    }
}

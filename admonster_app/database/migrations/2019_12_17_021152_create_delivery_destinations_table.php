<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryDestinationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_destinations', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedTinyInteger('type')->comment('接続先タイプ（CONST.DESTINATION_TYPE）');
            $table->string('name', 256)->comment('名称');
            $table->string('path', 256)->comment('パス');
            $table->longText('connection_information')->comment('接続先情報');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE delivery_destinations COMMENT '納品先'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_destinations');
    }
}

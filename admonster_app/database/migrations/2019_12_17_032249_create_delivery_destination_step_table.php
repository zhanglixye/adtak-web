<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryDestinationStepTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_destination_step', function (Blueprint $table) {
            $table->unsignedBigInteger('step_id')->comment('作業ID');
            $table->foreign('step_id')->references('id')->on('steps')->onDelete('cascade');
            $table->unsignedBigInteger('delivery_destination_id')->comment('納品先ID');
            $table->foreign('delivery_destination_id')->references('id')
                ->on('delivery_destinations')->onDelete('cascade');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');

            //  主キー
            // 自動命名機能で名前が長すぎたので、キー名を指定
            $table->primary(['step_id', 'delivery_destination_id'], 'step_id_delivery_destination_id_primary');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE delivery_destination_step COMMENT '作業別納品先管理'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_destination_step');
    }
}

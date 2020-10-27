<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemConfigValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_config_values', function (Blueprint $table) {
            $table->unsignedBigInteger('item_config_id')->comment('作業項目設定ID');
            $table->foreign('item_config_id')->references('id')->on('item_configs')->onDelete('cascade');
            $table->smallInteger('sort')->comment('並び順');
            $table->primary(['item_config_id', 'sort']);
            $table->unsignedBigInteger('label_id')->comment('ラベルID');
            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');

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
        Schema::dropIfExists('item_config_values');
    }
}

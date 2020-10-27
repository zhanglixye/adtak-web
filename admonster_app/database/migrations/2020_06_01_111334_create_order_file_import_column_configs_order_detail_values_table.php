<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderFileImportColumnConfigsOrderDetailValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_file_import_column_configs_order_detail_values', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('order_detail_value_id')->unique('order_detail_value_id_unique')->comment('案件明細の内容ID');
            $table->foreign('order_detail_value_id', 'order_detail_value_id_foreign')->references('id')
                ->on('order_detail_values')->onDelete('cascade');
            $table->unsignedBigInteger('order_file_import_column_config_id')->comment('案件ファイル取込列設定ID');
            $table->foreign('order_file_import_column_config_id', 'order_file_import_column_config_id_foreign')
                ->references('id')->on('order_file_import_column_configs')->onDelete('cascade');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });
        // テーブルコメント定義
        DB::statement("ALTER TABLE order_file_import_column_configs_order_detail_values COMMENT 'ファイル取込メイン設定と案件明細の内容の中間テーブル'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_file_import_column_configs_order_detail_values');
    }
}

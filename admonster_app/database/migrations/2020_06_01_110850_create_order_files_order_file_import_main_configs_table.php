<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderFilesOrderFileImportMainConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_files_order_file_import_main_configs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('order_file_id')->unique()->comment('案件ファイルID');
            $table->foreign('order_file_id')->references('id')
                ->on('order_files')->onDelete('cascade');
            $table->unsignedBigInteger('order_file_import_main_config_id')
                ->unique('order_file_import_main_config_id_unique')->comment('案件ファイル取込メイン設定ID');
            $table->foreign('order_file_import_main_config_id', 'related_order_file_import_main_config_id_foreign')
                ->references('id')->on('order_file_import_main_configs')->onDelete('cascade');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });
        // テーブルコメント定義
        DB::statement("ALTER TABLE order_files_order_file_import_main_configs COMMENT '案件ファイルとファイル取込メイン設定の中間テーブル'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_files_order_file_import_main_configs');
    }
}

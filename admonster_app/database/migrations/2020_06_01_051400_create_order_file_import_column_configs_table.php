<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderFileImportColumnConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_file_import_column_configs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('order_file_import_main_config_id')->comment('案件ファイル取込メイン設定ID');
            $table->foreign('order_file_import_main_config_id', 'order_file_import_main_config_id_foreign')
                ->references('id')->on('order_file_import_main_configs')->onDelete('cascade');
            $table->string('column', 256)->comment('列名');
            $table->mediumText('item', 50000)->nullable()->comment('項目名');
            $table->unsignedBigInteger('label_id')->comment('表示名');
            $table->unsignedSmallInteger('item_type')->comment('項目種別');
            $table->longText('rule')->nullable()->comment('ルール');
            $table->boolean('is_output')->default(1)->comment('レポートへの出力対象');
            $table->unsignedSmallInteger('sort')->comment('並び順');
            $table->unsignedSmallInteger('subject_part_no')->nullable()->comment('件名パート番号');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE order_file_import_column_configs COMMENT '案件ファイル取込列設定'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_file_import_column_configs');
    }
}

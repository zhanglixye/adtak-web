<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileImportColumnConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_import_column_configs', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('file_import_main_config_id')->comment('ファイル取込メイン設定ID');
            $table->foreign('file_import_main_config_id')->references('id')->on('file_import_main_configs')->onDelete('cascade');
            $table->unsignedBigInteger('label_id')->comment('ラベルID');
            $table->string('column', 256)->comment('列名');
            $table->smallInteger('item_type')->comment('項目種別');
            $table->smallInteger('data_type')->comment('データ型');
            $table->bigInteger('min')->nullable()->comment('最小値');
            $table->bigInteger('max')->nullable()->comment('最大値');
            $table->boolean('is_required')->default(0)->comment('必須(0:false, 1:true)');
            $table->boolean('is_active')->default(1)->comment('有効(0:無効データ, 1:有効データ)');
            $table->smallInteger('sort')->nullable()->comment('並び順');
            $table->smallInteger('subject_part_no')->nullable()->comment('件名パート番号');
            $table->smallInteger('request_info_type')->nullable()->comment('依頼情報種別');

            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE file_import_column_configs COMMENT 'ファイル取込列設定'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_import_column_configs');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_files', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_id')->comment('納品ID');
            $table->foreign('delivery_id')->references('id')->on('deliveries')->onDelete('cascade');
            $table->unsignedBigInteger('seq_no')->comment('シーケンス番号');
            $table->string('name', 256)->nullable()->comment('ファイル名');
            $table->string('file_path', 256)->nullable()->comment('ファイルパス');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');

            //  主キー
            $table->primary(['delivery_id', 'seq_no']);
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE delivery_files COMMENT '納品ファイル'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_files');
    }
}

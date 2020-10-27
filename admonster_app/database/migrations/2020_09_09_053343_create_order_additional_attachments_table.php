<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderAdditionalAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_additional_attachments', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('order_additional_info_id')->comment('案件補足情報ID');
            $table->foreign('order_additional_info_id')->references('id')->on('order_additional_infos')->onDelete('cascade');
            $table->string('name', 256)->nullable()->comment('ファイル名');
            $table->string('file_path', 256)->nullable()->comment('ファイルパス');
            $table->unsignedInteger('size')->nullable()->comment('サイズ(byte)');
            $table->unsignedSmallInteger('width')->nullable()->comment('幅(px)');
            $table->unsignedSmallInteger('height')->nullable()->comment('高さ(px)');
            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');
            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE order_additional_attachments COMMENT '案件補足情報ファイル'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_additional_attachments');
    }
}

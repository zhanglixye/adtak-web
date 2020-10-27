<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestWorkAdditionalInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_work_additional_infos', function (Blueprint $table) {
            $table->unsignedBigInteger('request_work_id')->comment('依頼作業ID');
            $table->foreign('request_work_id')->references('id')->on('request_works')->onDelete('cascade');
            $table->unsignedBigInteger('request_additional_info_id')->comment('依頼補足情報ID');
            $table->foreign('request_additional_info_id')->references('id')->on('request_additional_infos')->onDelete('cascade');
            $table->primary(['request_work_id', 'request_additional_info_id'], 'req_work_and_info_id_primary');
            
            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');
            
            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE request_work_additional_infos COMMENT '依頼作業補足情報'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_work_additional_infos');
    }
}

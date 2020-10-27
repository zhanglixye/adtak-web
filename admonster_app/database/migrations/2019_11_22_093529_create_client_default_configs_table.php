<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientDefaultConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_default_configs', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->comment('業務ID');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->boolean('is_viewable_request_related_mails')->default(0)->comment('依頼関連メール閲覧(0:false, 1:true)');
            $table->boolean('is_viewable_request_additional_infos')->default(0)->comment('依頼補足情報閲覧(0:false, 1:true)');
            $table->primary(['business_id']);

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE client_default_configs COMMENT 'クライアント初期設定テーブル'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_default_configs');
    }
}

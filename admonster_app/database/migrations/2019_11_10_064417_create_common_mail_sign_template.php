<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommonMailSignTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('common_mail_sign_templates', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('business_id')->comment('業務ID');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->comment('ユーザーID');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('title')->comment('タイトル');
            $table->longText('content')->comment('コンテンツ')->nullable(true);
            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');
            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
            $table->index(['user_id', 'business_id']);
        });
        // テーブルコメント定義
        DB::statement("ALTER TABLE common_mail_sign_templates COMMENT 'メール共通処理の署名設定'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('common_mail_sign_templates');
    }
}

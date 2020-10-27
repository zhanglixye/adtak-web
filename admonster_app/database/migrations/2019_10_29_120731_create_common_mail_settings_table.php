<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommonMailSettingsTable extends Migration
{
    /**
     * Run the migrations
     *
     * @return void
     */
    public function up()
    {
        Schema::create('common_mail_settings', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('business_id')->index()->comment('業務ID');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->integer('mail_to')->default(31)->comment('「Mail to」機能設定,5つステータスの組み合わせ値([16:popup]+[8:typing]+[4:default]+[2:enable]+[1:display])');
            $table->integer('cc')->default(31)->comment('「CC」機能設定,5つステータスの組み合わせ値([16:popup]+[8:typing]+[4:default]+[2:enable]+[1:display])');
            $table->integer('subject')->default(7)->comment('「件名」機能設定,3つステータスの組み合わせ値([4:default]+[2:enable]+[1:display])');
            $table->integer('body')->default(7)->comment('「本文」機能設定,3つステータスの組み合わせ値(4:default+2:enable+1:display)');
            $table->integer('mail_template')->default(7)->comment('「メールテンプレート」機能設定,3つステータスの組み合わせ値(4:default+2:enable+1:display)');
            $table->integer('sign_template')->default(7)->comment('「署名」機能設定,3つステータスの組み合わせ値(4:default+2:enable+1:display)');
            $table->integer('file_attachment')->default(3)->comment('「ファイル添付」機能設定,3つステータスの組み合わせ値(4:default+2:enable+1:display)');
            $table->integer('check_list_button')->default(3)->comment('「作業チェック」機能設定,3つステータスの組み合わせ値(4:default+2:enable+1:display)');
            $table->integer('review')->default(3)->comment('「レビュー」機能設定,3つステータスの組み合わせ値(2:enable+1:display)');
            $table->integer('use_time')->default(3)->comment('「作業時間」機能設定,3つステータスの組み合わせ値(4:default+2:enable+1:display)');
            $table->integer('unknown')->default(3)->comment('「不明あり」機能設定,3つステータスの組み合わせ値(4:default+2:enable+1:display)');
            $table->integer('save_button')->default(3)->comment('「保存」機能設定,2つステータスの組み合わせ値(2:enable+1:display)');

            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');
            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE common_mail_settings COMMENT 'メール共通処理設定'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('common_mail_settings');
    }
}

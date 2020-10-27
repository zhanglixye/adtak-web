<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommonMailFrequenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('common_mail_frequencies', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('business_id')->comment('業務ID');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->unsignedBigInteger('company_id')->comment('企業ID');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedBigInteger('step_id')->comment('作業ID');
            $table->foreign('step_id')->references('id')->on('steps')->onDelete('cascade');
            $table->String('name', 32)->comment('名前')->nullable(true);
            $table->String('mail_account', 64)->comment('メールアカウント')->nullable(true);
            $table->unsignedBigInteger('to_times')->comment('mail to回数')->default(0);
            $table->unsignedBigInteger('cc_times')->comment('mail cc回数')->default(0);
            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');
            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
            $table->unique(['company_id', 'business_id', 'step_id', 'mail_account'], 'idx_uni_compId_bizId_stepId_acct');
        });
        // テーブルコメント定義
        DB::statement("ALTER TABLE common_mail_frequencies COMMENT 'メール共通処理の宛先の利用頻度'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('common_mail_frequencies');
    }
}

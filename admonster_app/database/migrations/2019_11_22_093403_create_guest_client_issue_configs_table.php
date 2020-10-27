<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuestClientIssueConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guest_client_issue_configs', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->comment('業務ID');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->boolean('is_auto')->nullable()->comment('自動発行(0:false(手動), 1:true(自動))');
            $table->primary(['business_id']);

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE guest_client_issue_configs COMMENT 'ゲストクライアント発行設定'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guest_client_issue_configs');
    }
}

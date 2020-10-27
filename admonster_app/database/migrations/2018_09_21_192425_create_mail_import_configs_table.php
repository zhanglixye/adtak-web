<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailImportConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_import_configs', function (Blueprint $table) {
            $table->unsignedBigInteger('step_id')->comment('作業ID');
            $table->foreign('step_id')->references('id')->on('steps')->onDelete('cascade');
            $table->unsignedBigInteger('account_id')->nullable()->comment('アカウントID');
            $table->foreign('account_id')->references('id')->on('import_mail_accounts')->onDelete('cascade');
            $table->primary(['step_id']);
            $table->string('condition_1', 256)->nullable()->comment('取込み条件1');
            $table->string('condition_2', 256)->nullable()->comment('取込み条件2');
            $table->string('condition_3', 256)->nullable()->comment('取込み条件3');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE mail_import_configs COMMENT 'メール取込み設定'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mail_import_configs');
    }
}

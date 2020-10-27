<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportMailAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_mail_accounts', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->tinyInteger('receive_type')->nullable()->comment('受信方式');
            $table->string('server', 256)->nullable()->comment('サーバー');
            $table->string('account', 256)->nullable()->comment('アカウント');
            $table->string('password', 256)->nullable()->comment('パスワード');
            $table->longText('data_key')->comment('データキー');
            $table->string('mail_box', 256)->nullable()->comment('メールボックス');
            
            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');
            
            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE import_mail_accounts COMMENT '取込みメールアカウント'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_mail_accounts');
    }
}

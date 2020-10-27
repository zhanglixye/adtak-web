<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuestClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guest_clients', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->string('email', 256)->comment('メールアドレス');
            $table->unsignedBigInteger('request_id')->comment('依頼ID');
            $table->foreign('request_id')->references('id')->on('requests')->onDelete('cascade');

            // auth
            $table->string('token', 256)->unique()->comment('トークン');
            $table->string('password', 256)->nullable()->comment('パスワード');
            $table->dateTime('expired_at')->nullable()->comment('パスワードの有効期限');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE guest_clients COMMENT 'ゲストクライアント'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guest_clients');
    }
}

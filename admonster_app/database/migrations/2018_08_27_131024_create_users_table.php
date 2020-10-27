<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->string('name', 256)->nullable()->comment('ユーザー名');
            $table->string('nickname', 256)->nullable()->comment('ニックネーム');
            $table->tinyInteger('sex')->nullable()->comment('性別');
            $table->date('birthday')->nullable()->comment('生年月日');
            $table->string('postal_code', 16)->nullable()->comment('郵便番号');
            $table->string('address', 256)->nullable()->comment('住所');
            $table->string('tel', 16)->nullable()->comment('電話番号');
            $table->string('email', 256)->unique()->comment('メールアドレス');
            $table->string('password', 256)->comment('パスワード');
            $table->dateTime('password_changed_date')->nullable()->comment('パスワード変更日時');
            $table->longText('remarks')->nullable()->comment('備考');

            // auth
            $table->rememberToken()->comment('リメンバートークン');

            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE users COMMENT 'ユーザー'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

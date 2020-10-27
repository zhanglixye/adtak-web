<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePasswordResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email', 256)->index()->comment('メールアドレス');
            $table->string('token', 256)->comment('トークン');
            $table->dateTime('created_at')->nullable()->comment('登録日時');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE password_resets COMMENT 'パスワードリセット'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('password_resets');
    }
}

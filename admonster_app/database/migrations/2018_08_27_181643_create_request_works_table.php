<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_works', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->string('name', 256)->comment('依頼作業名');
            $table->unsignedBigInteger('request_id')->comment('依頼ID');
            $table->foreign('request_id')->references('id')->on('requests')->onDelete('cascade');
            $table->bigInteger('before_work_id')->nullable()->comment('前作業ID');
            $table->bigInteger('step_id')->comment('作業ID');
            $table->string('client_name', 256)->nullable()->comment('依頼主名');
            $table->dateTime('from')->nullable()->comment('掲載期間開始');
            $table->dateTime('to')->nullable()->comment('掲載期間終了');
            $table->dateTime('deadline')->nullable()->comment('指定納期');
            $table->longText('remarks')->nullable()->comment('備考');
            
            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE request_works COMMENT '依頼作業'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_works');
    }
}

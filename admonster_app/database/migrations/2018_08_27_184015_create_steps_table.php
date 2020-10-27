<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('steps', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->string('name', 256)->nullable()->comment('作業名');
            $table->tinyInteger('step_type')->comment('作業種別');
            $table->string('url', 256)->nullable()->comment('画面URL');
            $table->longText('description')->nullable()->comment('説明');
            
            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');
            
            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE steps COMMENT '作業'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('steps');
    }
}

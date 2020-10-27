<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomStatusAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_status_attributes', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('custom_status_id')->comment('カスタムステータスID');
            $table->foreign('custom_status_id')->references('id')
                ->on('custom_statuses')->onDelete('cascade');
            $table->unsignedBigInteger('label_id')->comment('名前');
            $table->unsignedSmallInteger('sort')->nullable()->comment('並び順');
            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');
            $table->unique(['custom_status_id', 'sort']);

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });
        // テーブルコメント定義
        DB::statement("ALTER TABLE custom_status_attributes COMMENT 'カスタムステータスの属性'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_status_attributes');
    }
}

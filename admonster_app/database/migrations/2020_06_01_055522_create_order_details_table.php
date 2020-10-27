<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('order_id')->comment('案件ID');
            $table->foreign('order_id')->references('id')
                ->on('orders')->onDelete('cascade');
            $table->string('name', 256)->comment('件名');
            $table->unsignedBigInteger('custom_status_id')->nullable()->comment('カスタムステータス');
            $table->foreign('custom_status_id')->references('id')
                ->on('custom_statuses')->onDelete('set null');
            $table->boolean('is_active')->default(1)->comment('有効');
            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });

        // テーブルコメント定義
        DB::statement("ALTER TABLE order_details COMMENT '案件明細'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_details');
    }
}

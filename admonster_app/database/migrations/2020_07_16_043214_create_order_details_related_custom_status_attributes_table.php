<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderDetailsRelatedCustomStatusAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details_related_custom_status_attributes', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('order_detail_id')->comment('案件明細ID');
            $table->foreign('order_detail_id', 'order_detail_id_foreign')->references('id')
                ->on('order_details')->onDelete('cascade');
            $table->unsignedBigInteger('custom_status_id')->comment('カスタムステータスID');
            $table->foreign('custom_status_id', 'custom_status_id_foreign')->references('id')
                ->on('custom_statuses')->onDelete('cascade');
            $table->unsignedBigInteger('custom_status_attribute_id')->comment('カスタムステータスの属性ID');
            $table->foreign('custom_status_attribute_id', 'custom_status_attribute_id_foreign')->references('id')
                ->on('custom_status_attributes')->onDelete('cascade');
            $table->unique(['order_detail_id', 'custom_status_id'], 'order_detail_id_and_custom_status_id_unique');

            // 共通
            $table->dateTime('created_at')->comment('登録日時');
            $table->bigInteger('created_user_id')->comment('登録者');
            $table->dateTime('updated_at')->comment('更新日時');
            $table->bigInteger('updated_user_id')->comment('更新者');
        });
        // テーブルコメント定義
        $table_comment = '案件明細に関連するカスタムステータスの属性\n'
            .'未選択　->　レコード無し\n'
            .'選択　->　レコードあり\n'
            .'カスタムステータス　削除:true　->　レコードあり\n'
            .'カスタムステータス（属性） 削除:true ->　未選択と同じ';
        DB::statement("ALTER TABLE order_details_related_custom_status_attributes COMMENT '{$table_comment}'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_details_related_custom_status_attributes');
    }
}

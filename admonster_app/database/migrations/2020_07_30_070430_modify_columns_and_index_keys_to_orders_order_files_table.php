<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnsAndIndexKeysToOrdersOrderFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders_order_files', function (Blueprint $table) {
            $table->dropForeign(['order_id']); // unique key を削除するために削除
            $table->dropUnique(['order_id']);
            $table->foreign('order_id')->references('id') // 再設定
                ->on('orders')->onDelete('cascade');
            $table->unsignedSmallInteger('import_type')
                ->default(0)// 既存のデータにconst.FILE_IMPORT_TYPE.NEWを代入
                ->after('order_file_id')
                ->comment('取込種別');
        });

        Schema::table('orders_order_files', function (Blueprint $table) {
            $table->unsignedSmallInteger('import_type')->default(null)->change();// 本来のdefaultに変更
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders_order_files', function (Blueprint $table) {
            $table->unique(['order_id']);
            $table->dropColumn('import_type');
        });
    }
}

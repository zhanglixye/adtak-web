<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCustomStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('label_id')->comment('名前')->change();
            $table->unsignedSmallInteger('sort')->nullable()->change();
            $table->boolean('is_deleted')->default(0)->comment('削除(0:false, 1:true)')->after('sort');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('label_id')->comment('ステータス名')->change();
            $table->unsignedSmallInteger('sort')->nullable(false)->change();
            $table->dropColumn('is_deleted');
        });
    }
}

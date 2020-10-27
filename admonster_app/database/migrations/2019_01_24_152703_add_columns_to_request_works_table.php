<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToRequestWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_works', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->after('step_id')->comment('依頼作業の所属するグループのID');
            $table->foreign('group_id')->references('id')->on('request_work_groups')->onDelete('cascade');
            $table->boolean('is_active')->default(1)->after('remarks')->comment('有効(0:履歴データ, 1:最新有効データ)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_works', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
            $table->dropColumn('is_active');
        });
    }
}

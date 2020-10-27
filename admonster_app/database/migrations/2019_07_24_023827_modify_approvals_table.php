<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('approvals', function (Blueprint $table) {
            // add
            $table->tinyInteger('status')->after('request_work_id')->comment('ステータス(0:未承認、1:保留、2:承認済)');

            // delete
            $table->dropColumn(['user_id', 'approval_result', 'reject_reason']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('approvals', function (Blueprint $table) {
            // create
            $table->unsignedBigInteger('user_id')->after('request_work_id')->nullable()->comment('ユーザーID');
            $table->tinyInteger('approval_result')->after('user_id')->nullable()->comment('承認結果(0:却下、1:承認、2:中止)');
            $table->longText('reject_reason')->after('result_type')->nullable()->comment('却下事由');

            // delete
            $table->dropColumn('status');
        });
    }
}

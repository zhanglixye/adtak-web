<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnsToWorkTimeSummaries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_time_summaries', function (Blueprint $table) {
            $table->unsignedBigInteger('request_id')->nullable()->after('step_id')->comment('依頼ID');
            $table->unsignedBigInteger('request_work_id')->nullable()->after('request_id')->comment('依頼作業ID');
            $table->date('actual_date')->comment('イベントの実績日')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_time_summaries', function (Blueprint $table) {
            $table->dropColumn('request_id');
            $table->dropColumn('request_work_id');
            $table->dateTime('actual_date')->comment('イベントの実績日(yyyy-mm-dd)');
        });
    }
}

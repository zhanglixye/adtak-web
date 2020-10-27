<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnsToTaskWorkTimeSummaries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('task_work_time_summaries', function (Blueprint $table) {
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
        Schema::table('task_work_time_summaries', function (Blueprint $table) {
            $table->dateTime('actual_date')->comment('イベントの実績日(yyyy-mm-dd)');
        });
    }
}

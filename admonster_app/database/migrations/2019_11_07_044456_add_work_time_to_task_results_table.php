<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWorkTimeToTaskResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('task_results', function (Blueprint $table) {
            $table->unsignedDecimal('work_time', 6, 2)->after('finished_at')->nullable()->comment('作業時間');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('task_results', function (Blueprint $table) {
            $table->dropColumn('work_time');
        });
    }
}

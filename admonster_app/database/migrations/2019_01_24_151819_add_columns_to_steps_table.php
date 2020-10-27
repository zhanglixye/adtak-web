<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('steps', function (Blueprint $table) {
            $table->tinyInteger('end_criteria')->after('deadline_limit')->comment('終了判定基準(0:全て, 1:グループ内)');
            $table->bigInteger('regect_step_id')->nullable()->after('end_criteria')->comment('却下時作業ID');
            $table->bigInteger('stop_step_id')->nullable()->after('regect_step_id')->comment('中止時作業ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('steps', function (Blueprint $table) {
            $table->dropColumn('end_criteria');
            $table->dropColumn('regect_step_id');
            $table->dropColumn('stop_step_id');
        });
    }
}

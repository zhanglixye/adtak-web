<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnsForStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('steps', function (Blueprint $table) {
            $table->smallInteger('time_unit')->after('description')->nullable()->comment('時間単位(1:minute, 2:hour, 3:day)');
            $table->smallInteger('deadline_limit')->comment('期限')->change();
            $table->dropColumn('regect_step_id');
            $table->dropColumn('stop_step_id');
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
            $table->dropColumn('time_unit');
            $table->smallInteger('deadline_limit')->comment('期限日数')->change();
            $table->bigInteger('regect_step_id')->nullable()->after('end_criteria')->comment('却下時作業ID');
            $table->bigInteger('stop_step_id')->nullable()->after('regect_step_id')->comment('中止時作業ID');
        });
    }
}

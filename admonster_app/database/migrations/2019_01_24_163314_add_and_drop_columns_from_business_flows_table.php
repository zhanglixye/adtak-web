<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAndDropColumnsFromBusinessFlowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_flows', function (Blueprint $table) {
            $table->dropColumn('regect_step_id');
            $table->dropColumn('stop_step_id');
            $table->bigInteger('next_step_id')->nullable()->after('business_id')->comment('次作業ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_flows', function (Blueprint $table) {
            $table->dropColumn('next_step_id');
            $table->bigInteger('regect_step_id')->nullable()->comment('却下時作業ID');
            $table->bigInteger('stop_step_id')->nullable()->comment('中止時作業ID');
        });
    }
}

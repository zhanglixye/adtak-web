<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToWorkTimeBusinessReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_time_business_reports', function (Blueprint $table) {
            //
            $table->bigIncrements('id')->first()->comment('ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_time_business_reports', function (Blueprint $table) {
            //
            $table->dropColumn('id');
        });
    }
}

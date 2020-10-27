<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBusinessIdToWorkTimeDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_time_details', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('business_id')->nullable()->after('task_id')->comment('業務ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_time_details', function (Blueprint $table) {
            //
            $table->dropColumn('business_id');
        });
    }
}

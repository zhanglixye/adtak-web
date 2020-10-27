<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBusinessFlowsTableAddPrimaryToSeqNo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `business_flows` DROP PRIMARY KEY, ADD PRIMARY KEY (`step_id`, `seq_no`)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `business_flows` DROP PRIMARY KEY, ADD PRIMARY KEY (`step_id`)");
    }
}

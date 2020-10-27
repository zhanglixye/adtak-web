<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // add
            $table->unsignedBigInteger('approval_task_id')->nullable()->after('id');
            $table->foreign('approval_task_id')->references('id')->on('approval_tasks')->onDelete('cascade');

            // delete
            $table->dropForeign(['approval_id']);
            $table->dropColumn('approval_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // create
            $table->unsignedBigInteger('approval_id')->nullable()->after('id')->comment('承認ID');
            $table->foreign('approval_id')->references('id')->on('approvals')->onDelete('cascade');

            // delete
            $table->dropForeign(['approval_task_id']);
            $table->dropColumn('approval_task_id');
        });
    }
}

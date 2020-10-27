<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSendMailFlgsToStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('steps', function (Blueprint $table) {
            $table->boolean('is_send_import_req_mail')->default(0)->after('stop_step_id')->comment('取込依頼メール送信フラグ(0:false, 1:true)');
            $table->boolean('is_send_allocation_req_mail')->default(0)->after('is_send_import_req_mail')->comment('割振依頼メール送信フラグ(0:false, 1:true)');
            $table->boolean('is_send_task_req_mail')->default(0)->after('is_send_allocation_req_mail')->comment('タスク実施依頼メール送信フラグ(0:false, 1:true)');
            $table->boolean('is_send_approval_req_mail')->default(0)->after('is_send_task_req_mail')->comment('承認依頼メール送信フラグ(0:false, 1:true)');
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
            $table->dropColumn('is_send_import_req_mail');
            $table->dropColumn('is_send_allocation_req_mail');
            $table->dropColumn('is_send_task_req_mail');
            $table->dropColumn('is_send_approval_req_mail');
        });
    }
}

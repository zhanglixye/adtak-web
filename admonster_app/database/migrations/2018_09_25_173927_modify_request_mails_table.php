<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyRequestMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_mails', function (Blueprint $table) {
            $table->dropColumn('step_id');
            $table->bigInteger('mail_account_id')->nullable()->after('id')->comment('メールアカウントID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_mails', function (Blueprint $table) {
            $table->unsignedBigInteger('step_id')->nullable()->comment('作業ID');
            $table->dropColumn('mail_account_id');
        });
    }
}

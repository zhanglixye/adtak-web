<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsOpenToWorkToRequestRelatedMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_related_mails', function (Blueprint $table) {
            $table->boolean('is_open_to_work')->default(0)->after('is_open_to_client')->comment('作業画面への公開(0:false, 1:true)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_related_mails', function (Blueprint $table) {
            $table->dropColumn('is_open_to_work');
        });
    }
}

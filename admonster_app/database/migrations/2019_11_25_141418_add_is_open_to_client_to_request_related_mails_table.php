<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsOpenToClientToRequestRelatedMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_related_mails', function (Blueprint $table) {
            $table->boolean('is_open_to_client')->default(0)->after('request_mail_id')->comment('クライアントへの公開(0:false, 1:true)');
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
            $table->dropColumn('is_open_to_client');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsOpenToClientToRequestAdditionalInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_additional_infos', function (Blueprint $table) {
            $table->boolean('is_open_to_client')->default(0)->after('content')->comment('クライアントへの公開(0:false, 1:true)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_additional_infos', function (Blueprint $table) {
            $table->dropColumn('is_open_to_client');
        });
    }
}

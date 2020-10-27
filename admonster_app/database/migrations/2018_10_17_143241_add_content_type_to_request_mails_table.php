<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContentTypeToRequestMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_mails', function (Blueprint $table) {
            $table->tinyInteger('content_type')->nullable()->after('subject')->comment('Content-Type(1:text/plain, 2:text/html)');
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
            $table->dropColumn('content_type');
        });
    }
}

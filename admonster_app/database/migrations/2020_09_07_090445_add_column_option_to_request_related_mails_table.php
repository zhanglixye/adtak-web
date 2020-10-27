<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnOptionToRequestRelatedMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_related_mails', function (Blueprint $table) {
            $table->longText('from')->nullable()->after('is_open_to_work')->comment('送信元');
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
            $table->dropColumn('from');
        });
    }
}

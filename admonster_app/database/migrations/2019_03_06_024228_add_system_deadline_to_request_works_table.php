<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSystemDeadlineToRequestWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_works', function (Blueprint $table) {
            $table->dateTime('system_deadline')->nullable()->after('deadline')->comment('システム納期');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_works', function (Blueprint $table) {
            $table->dropColumn('system_deadline');
        });
    }
}

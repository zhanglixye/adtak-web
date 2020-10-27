<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCodeToRequestWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_works', function (Blueprint $table) {
            $table->string('code', 256)->nullable()->after('id')->comment('取込みデータ番号');
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
            $table->dropColumn('code');
        });
    }
}

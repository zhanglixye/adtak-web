<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToRequestFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_files', function (Blueprint $table) {
            $table->bigInteger('step_id')->after('id')->comment('作業ID');
            $table->longText('err_description')->nullable()->after('create_status')->comment('エラー内容');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_files', function (Blueprint $table) {
            $table->dropColumn('step_id');
            $table->dropColumn('err_description');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContentAndRowNoToRequestWorkFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_work_files', function (Blueprint $table) {
            $table->longText('content')->nullable()->after('request_file_id')->comment('取込内容');
            $table->smallInteger('row_no')->nullable()->after('content')->comment('取込行番号');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_work_files', function (Blueprint $table) {
            $table->dropColumn('content');
            $table->dropColumn('row_no');
        });
    }
}

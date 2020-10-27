<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDiffCheckLevelItemConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_configs', function (Blueprint $table) {
            $table->tinyInteger('diff_check_level')->default(1)->after('is_required')->comment('差分チェックレベル(0:none, 1:error, 2:warning)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_configs', function (Blueprint $table) {
            $table->dropColumn('diff_check_level');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyItemConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_configs', function (Blueprint $table) {
            $table->dropColumn('is_file');
            $table->smallInteger('item_type')->after('name')->comment('項目種別');
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
            $table->boolean('is_file')->nullable()->comment('ファイル');
            $table->dropColumn('item_type');
        });
    }
}
